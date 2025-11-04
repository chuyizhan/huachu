<?php

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserSubscriptionController extends Controller
{
    /**
     * Display a listing of the user's creator subscriptions.
     */
    public function index()
    {
        $user = Auth::user();

        $subscriptions = UserSubscription::with([
            'creator.creatorProfile',
            'creatorSubscriptionPlan'
        ])
            ->where('subscriber_id', $user->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('UserSubscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Display subscriptions received by the creator (people who subscribed to them).
     */
    public function mySubscribers()
    {
        $user = Auth::user();

        // Only creators can access this
        if (!$user->is_creator) {
            abort(403, 'Only creators can access this page');
        }

        $subscribers = UserSubscription::with([
            'subscriber',
            'creatorSubscriptionPlan'
        ])
            ->where('creator_id', $user->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('UserSubscriptions/MySubscribers', [
            'subscribers' => $subscribers,
        ]);
    }
}
