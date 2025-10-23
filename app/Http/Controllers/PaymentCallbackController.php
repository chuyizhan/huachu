<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle async payment callback (webhook)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        Log::info('Payment callback received', $request->all());

        $params = $request->all();

        // Verify signature
        if (!$this->paymentService->verifyCallback($params)) {
            Log::error('Payment callback signature verification failed', $params);
            return response('FAIL', 400);
        }

        // Extract parameters according to payment gateway's format
        $orderNumber = $params['partnerorderid'] ?? null; // Our order number
        $tradeNumber = $params['orderno'] ?? null; // Gateway's order number
        $status = $params['orderstatus'] ?? null;
        $amount = isset($params['payamount']) ? (int)$params['payamount'] : null;

        if (!$orderNumber) {
            Log::error('Payment callback missing order number', $params);
            return response('FAIL', 400);
        }

        // Find the order
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            Log::error('Payment callback order not found', ['order_number' => $orderNumber]);
            return response('FAIL', 404);
        }

        // Check if already processed
        if ($order->status === 'completed') {
            Log::info('Payment callback order already completed', ['order_number' => $orderNumber]);
            return response('SUCCESS', 200);
        }

        // Verify amount matches
        $expectedAmount = (int)($order->amount * 100);
        if ($amount !== $expectedAmount) {
            Log::error('Payment callback amount mismatch', [
                'order_number' => $orderNumber,
                'expected' => $expectedAmount,
                'received' => $amount
            ]);
            return response('FAIL', 400);
        }

        DB::beginTransaction();
        try {
            // Create or update payment record
            $payment = Payment::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'trade_number' => $tradeNumber,
                ],
                [
                    'user_id' => $order->user_id,
                    'trade_number' => $tradeNumber,
                    'gateway' => 'third_party',
                    'payment_method' => $order->payment_method ?? 'unknown',
                    'amount' => $order->amount,
                    'actual_amount' => $amount / 100, // Convert cents to yuan
                    'fee' => 0, // Fee info not provided by gateway
                    'payer_ip' => $request->ip(),
                    'callback_data' => $params,
                    'status' => $status == '1' ? 'completed' : 'failed',
                    'paid_at' => $status == '1' ? now() : null,
                ]
            );

            // Update order status based on payment status
            if ($status == '1') {
                // Payment successful
                $order->status = 'completed';
                $order->paid_at = now();
                $order->payment_info = array_merge(
                    is_array($order->payment_info) ? $order->payment_info : [],
                    [
                        'trade_number' => $tradeNumber,
                        'payment_id' => $payment->id,
                        'callback_data' => $params,
                        'callback_at' => now()->toDateTimeString(),
                    ]
                );
                $order->save();

                // Handle order fulfillment based on order type
                $this->fulfillOrder($order);

                Log::info('Payment callback order completed', [
                    'order_number' => $orderNumber,
                    'payment_id' => $payment->id
                ]);
            } else {
                // Payment failed
                $order->status = 'failed';
                $order->payment_info = array_merge(
                    is_array($order->payment_info) ? $order->payment_info : [],
                    [
                        'trade_number' => $tradeNumber,
                        'payment_id' => $payment->id,
                        'callback_data' => $params,
                        'callback_at' => now()->toDateTimeString(),
                        'failure_reason' => $params['remark'] ?? 'Unknown'
                    ]
                );
                $order->save();

                Log::warning('Payment callback order failed', [
                    'order_number' => $orderNumber,
                    'payment_id' => $payment->id,
                    'reason' => $params['remark'] ?? 'Unknown'
                ]);
            }

            DB::commit();
            return response('SUCCESS', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment callback processing exception', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('FAIL', 500);
        }
    }

    /**
     * Handle sync payment return (redirect)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function returnUrl(Request $request)
    {
        Log::info('Payment return received', $request->all());

        $params = $request->all();

        // Verify signature
        if (!$this->paymentService->verifyCallback($params)) {
            Log::error('Payment return signature verification failed', $params);
            return redirect()->route('recharge.index')->withErrors(['payment' => '支付验证失败']);
        }

        $orderNumber = $params['partnerorderid'] ?? null; // Our order number
        $status = $params['orderstatus'] ?? null;

        if (!$orderNumber) {
            return redirect()->route('recharge.index')->withErrors(['payment' => '订单号缺失']);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect()->route('recharge.index')->withErrors(['payment' => '订单不存在']);
        }

        if ($status == '1') {
            // Payment successful - redirect based on order type
            if ($order->type === 'recharge') {
                return redirect()->route('recharge.success', ['order' => $order->id])
                    ->with('success', '充值成功！');
            } elseif ($order->type === 'subscription') {
                return redirect()->route('plan-subscriptions.show', ['id' => $order->related_id])
                    ->with('success', '订阅成功！');
            }

            return redirect()->route('profile')->with('success', '支付成功！');
        }

        // Payment failed or pending
        return redirect()->route('recharge.index')->withErrors(['payment' => '支付失败或等待处理']);
    }

    /**
     * Fulfill order based on type
     *
     * @param Order $order
     * @return void
     */
    protected function fulfillOrder(Order $order): void
    {
        switch ($order->type) {
            case 'recharge':
                // Add credits to user
                $user = $order->user;
                if ($user) {
                    $paymentInfo = is_array($order->payment_info) ? $order->payment_info : [];
                    $bonus = $paymentInfo['bonus_credits'] ?? 0;
                    $packageId = $paymentInfo['package_id'] ?? null;

                    // Add base amount
                    $user->credits += $order->amount;
                    $user->save();

                    // Create credit transaction for recharge
                    \App\Models\CreditTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'earned',
                        'credits' => $order->amount,
                        'reason' => 'recharge',
                        'metadata' => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'payment_method' => $order->payment_method,
                            'package_id' => $packageId,
                        ],
                        'related_type' => Order::class,
                        'related_id' => $order->id,
                    ]);

                    // Add bonus if exists
                    if ($bonus > 0) {
                        $user->credits += $bonus;
                        $user->save();

                        \App\Models\CreditTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'earned',
                            'credits' => $bonus,
                            'reason' => 'recharge_bonus',
                            'metadata' => [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'package_id' => $packageId,
                                'base_amount' => $order->amount,
                            ],
                            'related_type' => Order::class,
                            'related_id' => $order->id,
                        ]);
                    }

                    Log::info('Credits added to user', [
                        'user_id' => $user->id,
                        'base_amount' => $order->amount,
                        'bonus' => $bonus,
                        'total' => $order->amount + $bonus
                    ]);
                }
                break;

            case 'subscription':
                // Subscription is already created when order is created
                // The subscription status should be updated if needed
                Log::info('Subscription order fulfilled', ['order_id' => $order->id]);
                break;

            case 'post_purchase':
                // Post purchase is already recorded
                Log::info('Post purchase order fulfilled', ['order_id' => $order->id]);
                break;

            default:
                Log::warning('Unknown order type for fulfillment', [
                    'order_id' => $order->id,
                    'type' => $order->type
                ]);
                break;
        }
    }
}
