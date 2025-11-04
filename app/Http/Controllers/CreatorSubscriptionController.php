<?php

namespace App\Http\Controllers;

use App\Models\CreatorSubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\CreatorSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class CreatorSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(CreatorSubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Subscribe to a creator's plan.
     */
    public function subscribe(Request $request, CreatorSubscriptionPlan $plan)
    {
        $user = Auth::user();

        // Prevent users from subscribing to themselves
        if ($user->id === $plan->creator_id) {
            return redirect()->back()->with('error', '不能订阅自己的内容');
        }

        try {
            $subscription = $this->subscriptionService->subscribe($user, $plan);

            return redirect()->back()->with('success', '订阅成功！');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(UserSubscription $subscription)
    {
        if ($subscription->subscriber_id !== Auth::id()) {
            abort(403, '无权限操作');
        }

        $this->subscriptionService->cancel($subscription, '用户取消');

        return redirect()->back()->with('success', '已取消订阅');
    }

    /**
     * Get user's active subscriptions.
     */
    public function mySubscriptions()
    {
        $subscriptions = $this->subscriptionService->getActiveSubscriptions(Auth::user());

        return inertia('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
