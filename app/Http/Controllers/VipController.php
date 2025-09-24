<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\VipTier;
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
        $tiers = VipTier::active()
            ->orderBy('sort_order')
            ->get();

        $user = Auth::user();
        $currentSubscription = $user->subscriptions()
            ->with('vipTier')
            ->where('status', 'active')
            ->where('type', 'tier')
            ->first();

        return Inertia::render('Vip/Index', [
            'tiers' => $tiers,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    public function show($slug)
    {
        $tier = VipTier::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $user = Auth::user();
        $hasSubscription = $user->subscriptions()
            ->where('vip_tier_id', $tier->id)
            ->where('status', 'active')
            ->exists();

        return Inertia::render('Vip/Show', [
            'tier' => $tier,
            'hasSubscription' => $hasSubscription,
        ]);
    }

    public function subscribe(Request $request, $slug)
    {
        $tier = VipTier::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $user = Auth::user();

        // Check if user already has an active subscription to this tier
        $existingSubscription = $user->subscriptions()
            ->where('vip_tier_id', $tier->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return redirect()->route('vip.show', $slug)
                ->with('error', 'You already have an active subscription to this tier.');
        }

        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $amount = $request->billing_cycle === 'yearly' ? $tier->yearly_price : $tier->monthly_price;

        // Create subscription (in real app, integrate with payment processor)
        UserSubscription::create([
            'subscriber_id' => $user->id,
            'creator_id' => null,
            'vip_tier_id' => $tier->id,
            'type' => 'tier',
            'amount' => $amount,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $request->billing_cycle === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
        ]);

        return redirect()->route('vip.index')
            ->with('success', 'Successfully subscribed to ' . $tier->name . '!');
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
            ->with(['vipTier', 'creator.creatorProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Vip/MySubscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}