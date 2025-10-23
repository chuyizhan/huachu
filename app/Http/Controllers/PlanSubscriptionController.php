<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanSubscription;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlanSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of user's subscriptions.
     */
    public function index()
    {
        $user = Auth::user();

        $subscriptions = PlanSubscription::with('plan')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $activeSubscription = PlanSubscription::with('plan')
            ->where('user_id', $user->id)
            ->active()
            ->first();

        return Inertia::render('PlanSubscriptions/Index', [
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
        ]);
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create()
    {
        $plans = Plan::active()->orderBy('sort_order')->get();

        return Inertia::render('PlanSubscriptions/Create', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a newly created subscription in storage.
     */
    public function store(Request $request, \App\Services\PaymentService $paymentService)
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('VIP Subscription request received', [
            'user_id' => $user->id,
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|in:fake,alipay,wechat',
            'auto_renew' => 'boolean',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        // Check if user already has an active subscription
        $existingSubscription = PlanSubscription::where('user_id', $user->id)
            ->active()
            ->first();

        if ($existingSubscription) {
            return back()->withErrors([
                'subscription' => '您已有一个有效订阅，请先取消后再订阅新套餐。'
            ]);
        }

        DB::beginTransaction();
        try {
            // Create a pending subscription
            $subscription = PlanSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'price_paid' => $plan->price,
                'payment_method' => $validated['payment_method'],
                'started_at' => now(), // Set temporary started_at, will be updated on activation
                'auto_renew' => $request->boolean('auto_renew', false),
                'renewal_count' => 0,
            ]);

            // Create order record
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'type' => 'subscription',
                'amount' => $plan->price,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_info' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'period_days' => $plan->period_days,
                    'subscription_id' => $subscription->id,
                    'auto_renew' => $validated['auto_renew'] ?? false,
                ],
                'related_id' => $subscription->id,
                'related_type' => PlanSubscription::class,
            ]);

            // Handle different payment methods
            if ($validated['payment_method'] === 'fake') {
                // Fake payment for testing
                $payment = \App\Models\Payment::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'gateway' => 'fake',
                    'payment_method' => 'fake',
                    'amount' => $plan->price,
                    'actual_amount' => $plan->price,
                    'fee' => 0,
                    'status' => 'completed',
                    'payer_ip' => $request->ip(),
                    'paid_at' => now(),
                ]);

                $order->markAsCompleted();

                // Activate subscription
                $subscription->status = 'active';
                $subscription->started_at = now();
                $subscription->expires_at = now()->addDays($plan->period_days);
                $subscription->payment_transaction_id = 'FAKE' . time();
                $subscription->save();

                DB::commit();

                return redirect()->route('plan-subscriptions.show', $subscription->id)
                    ->with('success', '订阅成功！');
            } else {
                // Real payment (Alipay/WeChat)
                // Create pending payment record
                $payment = \App\Models\Payment::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'gateway' => 'third_party',
                    'payment_method' => $validated['payment_method'],
                    'amount' => $plan->price,
                    'actual_amount' => $plan->price,
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
                    \Illuminate\Support\Facades\Log::error('Failed to send payment initiated notification', [
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
            \Illuminate\Support\Facades\Log::error('VIP subscription creation failed', [
                'user_id' => $user->id,
                'plan_id' => $validated['plan_id'],
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['subscription' => '订阅创建失败：' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified subscription.
     */
    public function show($id)
    {
        $subscription = PlanSubscription::with('plan')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return Inertia::render('PlanSubscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit($id)
    {
        $subscription = PlanSubscription::with('plan')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return Inertia::render('PlanSubscriptions/Edit', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Update the specified subscription in storage.
     */
    public function update(Request $request, $id)
    {
        $subscription = PlanSubscription::where('user_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'auto_renew' => 'boolean',
        ]);

        $subscription->auto_renew = $request->boolean('auto_renew');
        $subscription->save();

        return redirect()->route('plan-subscriptions.show', $subscription->id)
            ->with('success', 'Subscription settings updated successfully!');
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request, $id)
    {
        $subscription = PlanSubscription::where('user_id', Auth::id())
            ->findOrFail($id);

        if ($subscription->isCancelled()) {
            return back()->withErrors(['subscription' => 'Subscription is already cancelled.']);
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $subscription->cancel($request->reason);

        return redirect()->route('plan-subscriptions.show', $subscription->id)
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Renew the subscription.
     */
    public function renew($id)
    {
        $user = Auth::user();
        $subscription = PlanSubscription::with('plan')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$subscription->plan) {
            return back()->withErrors(['subscription' => 'Plan not found.']);
        }

        if ($subscription->isActive() && !$subscription->auto_renew) {
            return back()->withErrors(['subscription' => 'Subscription is already active.']);
        }

        DB::beginTransaction();
        try {
            // Check if user has enough credits
            // Example: if ($user->credits < $subscription->plan->price) {
            //     return back()->withErrors(['subscription' => 'Insufficient credits.']);
            // }

            // Renew the subscription
            $subscription->renew();

            // Create an order record for the renewal
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'type' => 'subscription',
                'amount' => $subscription->plan->price,
                'status' => 'completed',
                'payment_method' => $subscription->payment_method,
                'payment_info' => json_encode([
                    'plan_id' => $subscription->plan->id,
                    'plan_name' => $subscription->plan->name,
                    'period_days' => $subscription->plan->period_days,
                    'renewal' => true,
                    'renewal_count' => $subscription->renewal_count,
                ]),
                'related_id' => $subscription->id,
                'related_type' => PlanSubscription::class,
                'paid_at' => now(),
            ]);

            // Deduct credits
            // Example: $user->credits -= $subscription->plan->price;
            // $user->save();

            DB::commit();

            return redirect()->route('plan-subscriptions.show', $subscription->id)
                ->with('success', 'Subscription renewed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['subscription' => 'Failed to renew subscription. Please try again.']);
        }
    }

    /**
     * Remove the specified subscription from storage (soft delete).
     */
    public function destroy($id)
    {
        $subscription = PlanSubscription::where('user_id', Auth::id())
            ->findOrFail($id);

        // Only allow deletion of cancelled or expired subscriptions
        if ($subscription->isActive()) {
            return back()->withErrors(['subscription' => 'Cannot delete an active subscription. Please cancel it first.']);
        }

        $subscription->delete();

        return redirect()->route('plan-subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }
}
