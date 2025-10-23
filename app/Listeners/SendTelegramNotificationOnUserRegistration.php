<?php

namespace App\Listeners;

use App\Notifications\NewUserRegistered;
use App\Services\TelegramNotifiable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramNotificationOnUserRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        try {
            $telegramNotifiable = new TelegramNotifiable();
            $telegramNotifiable->notify(new NewUserRegistered($event->user));
        } catch (\Exception $e) {
            // Log error but don't fail the registration
            Log::error('Failed to send Telegram notification for new user', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
