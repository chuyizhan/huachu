<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CreditTransaction;
use App\Models\RechargePackage;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RechargeController extends Controller
{
    /**
     * Show the recharge page with available packages
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Get active packages from database
        $packages = RechargePackage::active()
            ->ordered()
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'amount' => (float) $package->amount,
                    'bonus' => (float) $package->bonus,
                    'total' => $package->total,
                    'description' => $package->description,
                    'is_popular' => $package->is_popular,
                ];
            });

        return Inertia::render('Recharge/Index', [
            'packages' => $packages,
            'userCredits' => (float) $user->credits,
        ]);
    }

    /**
     * Process the recharge
     */
    public function process(Request $request, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|exists:recharge_packages,id',
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|in:fake,alipay,wechat',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Get package details if it's a package purchase
            $package = null;
            $baseAmount = $validated['amount'];
            Log::info('baseAmount', ['baseAmount' => $baseAmount]);
            $bonus = 0;

            if (isset($validated['package_id'])) {
                $package = RechargePackage::find($validated['package_id']);
                if ($package) {
                    $baseAmount = (float) $package->amount;
                    $bonus = (float) $package->bonus;
                }
            }

            // Create order - only charge the base amount
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'type' => 'recharge',
                'amount' => $baseAmount,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_info' => [
                    'package_id' => $package ? $package->id : null,
                    'bonus_credits' => $bonus,
                ],
            ]);

            // Handle different payment methods
            if ($validated['payment_method'] === 'fake') {
                // Simulate successful payment (fake/test mode)
                // Create payment record
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'gateway' => 'fake',
                    'payment_method' => 'fake',
                    'amount' => $baseAmount,
                    'actual_amount' => $baseAmount,
                    'fee' => 0,
                    'status' => 'completed',
                    'payer_ip' => $request->ip(),
                    'paid_at' => now(),
                ]);

                $order->markAsCompleted();

                // Add base amount credits to user
                $user->credits += $baseAmount;
                $user->save();

                // Create credit transaction for base amount (recharge)
                CreditTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'earned',
                    'credits' => $baseAmount,
                    'reason' => 'recharge',
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_id' => $payment->id,
                        'payment_method' => $validated['payment_method'],
                        'package_id' => $package ? $package->id : null,
                    ],
                    'related_type' => Order::class,
                    'related_id' => $order->id,
                ]);

                // If there's a bonus, add it separately and create another transaction
                if ($bonus > 0) {
                    $user->credits += $bonus;
                    $user->save();

                    CreditTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'earned',
                        'credits' => $bonus,
                        'reason' => 'recharge_bonus',
                        'metadata' => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'payment_id' => $payment->id,
                            'package_id' => $package->id,
                            'package_name' => $package->name,
                            'base_amount' => $baseAmount,
                        ],
                        'related_type' => Order::class,
                        'related_id' => $order->id,
                    ]);
                }

                DB::commit();

                return redirect()->route('recharge.success', ['order' => $order->id])
                    ->with('success', '充值成功！');
            } else {
                // Real payment (Alipay/WeChat)
                // Create pending payment record
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'gateway' => 'third_party',
                    'payment_method' => $validated['payment_method'],
                    'amount' => $baseAmount,
                    'actual_amount' => $baseAmount,
                    'fee' => 0,
                    'status' => 'pending',
                    'payer_ip' => $request->ip(),
                ]);

                DB::commit();

                // Send Telegram notification for payment initiation
                try {
                    $telegramNotifiable = new \App\Services\TelegramNotifiable();
                    $telegramNotifiable->notify(new \App\Notifications\PaymentInitiated($order));
                } catch (\Exception $e) {
                    Log::error('Failed to send payment initiated notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Get payment URL from payment gateway
                $paymentData = $paymentService->getPaymentParams(
                    $order,
                    $validated['payment_method'],
                    $request->ip()
                );

                if (!$paymentData || !$paymentData['success']) {
                    $errorMsg = $paymentData['message'] ?? '支付请求失败，请重试';
                    return back()->withErrors(['payment' => $errorMsg]);
                }

                // Update payment with gateway order ID if available
                if (isset($paymentData['gateway_order_id'])) {
                    $payment->update([
                        'trade_number' => $paymentData['gateway_order_id'],
                        'response_data' => $paymentData['data'],
                    ]);
                }

                // Use Inertia location visit for external redirect (full page redirect)
                return Inertia::location($paymentData['payurl']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['payment' => '充值失败：' . $e->getMessage()]);
        }
    }

    /**
     * Show recharge success page
     */
    public function success(Order $order): Response
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();

        return Inertia::render('Recharge/Success', [
            'order' => $order,
            'userCredits' => (float) $user->credits,
        ]);
    }

    /**
     * Show recharge history page
     */
    public function history(): Response
    {
        $user = Auth::user();

        // Get all recharge transactions for the user
        $transactions = CreditTransaction::where('user_id', $user->id)
            ->where('type', 'earned')
            ->where('reason', 'recharge')
            ->with('related')
            ->latest()
            ->paginate(20);

        // Transform to add order data
        $transactions->through(function ($transaction) {
            $data = $transaction->toArray();
            // If related is an Order, add it as 'order' for easier access in frontend
            if ($transaction->related instanceof Order) {
                $data['order'] = $transaction->related;
            }
            return $data;
        });

        return Inertia::render('Recharge/History', [
            'transactions' => $transactions,
            'userCredits' => (float) $user->credits,
        ]);
    }
}
