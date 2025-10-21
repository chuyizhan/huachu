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
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
            'payment_transaction_id' => 'nullable|string',
            'auto_renew' => 'boolean',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Check if user already has an active subscription
        $existingSubscription = PlanSubscription::where('user_id', $user->id)
            ->active()
            ->first();

        if ($existingSubscription) {
            return back()->withErrors([
                'subscription' => 'You already have an active subscription. Please cancel it before subscribing to a new plan.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Create the subscription
            $subscription = PlanSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'price_paid' => $plan->price,
                'payment_method' => $request->payment_method,
                'payment_transaction_id' => $request->payment_transaction_id,
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->period_days),
                'auto_renew' => $request->boolean('auto_renew', false),
                'renewal_count' => 0,
            ]);

            // Create an order record
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'type' => 'subscription',
                'amount' => $plan->price,
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_info' => json_encode([
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'period_days' => $plan->period_days,
                    'transaction_id' => $request->payment_transaction_id,
                ]),
                'related_id' => $subscription->id,
                'related_type' => PlanSubscription::class,
                'paid_at' => now(),
            ]);

            // Deduct credits or process payment here
            // Example: $user->credits -= $plan->price;
            // $user->save();

            DB::commit();

            return redirect()->route('plan-subscriptions.show', $subscription->id)
                ->with('success', 'Successfully subscribed to ' . $plan->name . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['subscription' => 'Failed to create subscription. Please try again.']);
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
