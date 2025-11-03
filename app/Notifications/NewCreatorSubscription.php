<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewCreatorSubscription extends Notification implements ShouldQueue
{
    use Queueable;

    protected UserSubscription $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        $subscription = $this->subscription->load(['subscriber', 'creator.creatorProfile', 'creatorSubscriptionPlan']);
        $subscriber = $subscription->subscriber;
        $creator = $subscription->creator;
        $plan = $subscription->creatorSubscriptionPlan;

        $message = "💰 *新订阅通知*\n\n";
        $message .= "*订阅者:* {$subscriber->name} (#{$subscriber->id})\n";
        $message .= "*创作者:* " . ($creator->creatorProfile?->display_name ?? $creator->name) . " (#{$creator->id})\n";

        if ($plan) {
            $message .= "*订阅计划:* {$plan->name}\n";
            $message .= "*时长:* {$plan->formatted_duration}\n";
        }

        $message .= "*订阅金额:* ¥" . number_format($subscription->creator_amount + $subscription->platform_amount, 2) . "\n";
        $message .= "*创作者收益:* ¥" . number_format($subscription->creator_amount, 2) . "\n";
        $message .= "*平台分成:* ¥" . number_format($subscription->platform_amount, 2) . "\n";
        $message .= "*订阅时间:* " . $subscription->created_at->format('Y-m-d H:i:s') . "\n";

        return TelegramMessage::create()
            ->token(config('services.telegram.bot_token'))
            ->to(config('services.telegram.chat_id'))
            ->content($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'subscriber_id' => $this->subscription->subscriber_id,
            'creator_id' => $this->subscription->creator_id,
            'amount' => $this->subscription->creator_amount + $this->subscription->platform_amount,
        ];
    }
}
