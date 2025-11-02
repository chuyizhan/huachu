<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreatorSubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class CreatorSubscriptionService
{
    /**
     * Subscribe a user to a creator's subscription plan.
     *
     * @param User $subscriber The user subscribing
     * @param CreatorSubscriptionPlan $plan The subscription plan
     * @return UserSubscription
     * @throws Exception
     */
    public function subscribe(User $subscriber, CreatorSubscriptionPlan $plan): UserSubscription
    {
        // Check if user has sufficient credits
        if ($subscriber->credits < $plan->price) {
            throw new Exception('积分不足，无法订阅');
        }

        // Check if already subscribed and active
        $existingSubscription = UserSubscription::where('subscriber_id', $subscriber->id)
            ->where('creator_id', $plan->creator_id)
            ->active()
            ->first();

        if ($existingSubscription) {
            throw new Exception('您已订阅该创作者');
        }

        // Get creator and commission rate
        $creator = User::findOrFail($plan->creator_id);
        $commissionRate = $creator->creatorProfile->platform_commission_rate ?? 30.00;

        // Calculate amounts
        $subscriptionPrice = $plan->price;
        $platformAmount = $subscriptionPrice * ($commissionRate / 100);
        $creatorAmount = $subscriptionPrice - $platformAmount;

        return DB::transaction(function () use (
            $subscriber,
            $creator,
            $plan,
            $subscriptionPrice,
            $platformAmount,
            $creatorAmount,
            $commissionRate
        ) {
            // Create subscription
            $subscription = UserSubscription::create([
                'subscriber_id' => $subscriber->id,
                'creator_id' => $creator->id,
                'creator_subscription_plan_id' => $plan->id,
                'type' => 'creator',
                'amount' => $subscriptionPrice,
                'platform_amount' => $platformAmount,
                'creator_amount' => $creatorAmount,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
            ]);

            // Deduct credits from subscriber
            $subscriber->decrement('credits', $subscriptionPrice);
            CreditTransaction::create([
                'user_id' => $subscriber->id,
                'type' => 'spent',
                'credits' => -$subscriptionPrice,
                'reason' => "订阅创作者：{$creator->name}",
                'related_type' => UserSubscription::class,
                'related_id' => $subscription->id,
            ]);

            // Credit to creator (net amount after commission)
            $creator->increment('credits', $creatorAmount);
            CreditTransaction::create([
                'user_id' => $creator->id,
                'type' => 'earned',
                'credits' => $creatorAmount,
                'reason' => "订阅收入",
                'related_type' => UserSubscription::class,
                'related_id' => $subscription->id,
            ]);

            // Credit platform commission (to system user ID 1 or track separately)
            if ($platformAmount > 0) {
                CreditTransaction::create([
                    'user_id' => 1, // Platform account
                    'type' => 'earned',
                    'credits' => $platformAmount,
                    'reason' => "平台佣金 - {$creator->name}",
                    'related_type' => UserSubscription::class,
                    'related_id' => $subscription->id,
                    'metadata' => [
                        'creator_id' => $creator->id,
                        'commission_rate' => $commissionRate,
                    ],
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Cancel a subscription.
     *
     * @param UserSubscription $subscription
     * @param string|null $reason
     * @return bool
     */
    public function cancel(UserSubscription $subscription, ?string $reason = null): bool
    {
        $subscription->status = 'cancelled';
        $subscription->cancelled_at = now();

        if ($reason) {
            $subscription->billing_cycle = $reason; // Temporarily store reason here
        }

        return $subscription->save();
    }

    /**
     * Check if a user has an active subscription to a creator.
     *
     * @param int $subscriberId
     * @param int $creatorId
     * @return bool
     */
    public function hasActiveSubscription(int $subscriberId, int $creatorId): bool
    {
        return UserSubscription::where('subscriber_id', $subscriberId)
            ->where('creator_id', $creatorId)
            ->active()
            ->exists();
    }

    /**
     * Get all active subscriptions for a subscriber.
     *
     * @param User $subscriber
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveSubscriptions(User $subscriber)
    {
        return UserSubscription::with(['creator', 'creatorSubscriptionPlan'])
            ->where('subscriber_id', $subscriber->id)
            ->active()
            ->get();
    }

    /**
     * Get subscriber count for a creator.
     *
     * @param User $creator
     * @return int
     */
    public function getSubscriberCount(User $creator): int
    {
        return UserSubscription::where('creator_id', $creator->id)
            ->active()
            ->distinct('subscriber_id')
            ->count('subscriber_id');
    }

    /**
     * Get total revenue for a creator.
     *
     * @param User $creator
     * @return float
     */
    public function getTotalRevenue(User $creator): float
    {
        return UserSubscription::where('creator_id', $creator->id)
            ->sum('creator_amount') ?? 0;
    }
}
