<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;

class VipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $plans = Plan::active()
            ->orderBy('sort_order')
            ->get();

        $user = Auth::user();
        $currentSubscription = $user->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where('type', 'plan')
            ->first();

        return Inertia::render('Vip/Index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    public function show($slug)
    {
        $plan = Plan::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $user = Auth::user();
        $hasSubscription = $user->subscriptions()
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->exists();

        return Inertia::render('Vip/Show', [
            'plan' => $plan,
            'hasSubscription' => $hasSubscription,
        ]);
    }

    public function subscribe(Request $request, $slug)
    {
        $plan = Plan::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $user = Auth::user();

        // Check if user already has an active subscription to this plan
        $existingSubscription = $user->subscriptions()
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return redirect()->route('vip.show', $slug)
                ->with('error', 'You already have an active subscription to this plan.');
        }

        // Calculate expiry based on plan period_days
        $expiresAt = now()->addDays($plan->period_days);

        // Create subscription (in real app, integrate with payment processor)
        UserSubscription::create([
            'subscriber_id' => $user->id,
            'creator_id' => null,
            'plan_id' => $plan->id,
            'type' => 'plan',
            'amount' => $plan->price,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('vip.index')
            ->with('success', 'Successfully subscribed to ' . $plan->name . '!');
    }

    public function cancel($subscriptionId)
    {
        $subscription = UserSubscription::where('subscriber_id', Auth::id())
            ->findOrFail($subscriptionId);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('vip.index')
            ->with('success', 'Subscription cancelled successfully.');
    }

    public function mySubscriptions()
    {
        $user = Auth::user();

        $subscriptions = $user->subscriptions()
            ->with(['plan', 'creator.creatorProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Vip/Subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}