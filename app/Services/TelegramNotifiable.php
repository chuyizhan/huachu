<?php

namespace App\Services;

use Illuminate\Notifications\Notifiable;

class TelegramNotifiable
{
    use Notifiable;

    /**
     * Route notifications for the Telegram channel.
     *
     * @return string
     */
    public function routeNotificationForTelegram(): string
    {
        return config('services.telegram.chat_id');
    }
}
