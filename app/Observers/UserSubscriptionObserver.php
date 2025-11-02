<?php

namespace App\Observers;

use App\Models\UserSubscription;
use App\Models\CreatorProfile;
use App\Notifications\NewCreatorSubscription;
use App\Services\TelegramNotifiable;
use Illuminate\Support\Facades\Notification;

class UserSubscriptionObserver
{
    /**
     * Handle the UserSubscription "created" event.
     */
    public function created(UserSubscription $subscription): void
    {
        // Update creator profile revenue when subscription is created
        if ($subscription->creator_id && $subscription->creator_amount && $subscription->platform_amount) {
            $creatorProfile = CreatorProfile::where('user_id', $subscription->creator_id)->first();

            if ($creatorProfile) {
                $creatorProfile->increment('total_earnings', $subscription->creator_amount);
                $creatorProfile->increment('total_platform_share', $subscription->platform_amount);
            }

            // Send Telegram notification
            Notification::send(new TelegramNotifiable(), new NewCreatorSubscription($subscription));
        }
    }

    /**
     * Handle the UserSubscription "updated" event.
     */
    public function updated(UserSubscription $subscription): void
    {
        // If subscription is cancelled or status changed, we might want to handle refunds here
        // For now, we'll keep the revenue as-is since the service was provided
    }

    /**
     * Handle the UserSubscription "deleted" event.
     */
    public function deleted(UserSubscription $subscription): void
    {
        // If we soft delete and want to reverse revenue, handle it here
        // For now, we keep revenue as-is even if subscription is deleted
    }
}
