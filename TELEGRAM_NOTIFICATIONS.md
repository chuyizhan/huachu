# Telegram Notification System

This application includes a Telegram notification system that sends messages to your Telegram channel/group when certain events occur.

## Features

- 🆕 Notifications when new posts are published
- 📱 Direct messages to your Telegram channel or group
- 🔔 Can be extended for other events (payments, subscriptions, etc.)
- ⚡ Queued notifications (won't slow down the application)

## Setup Instructions

### 1. Create a Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Send `/newbot` command
3. Follow the instructions to create your bot
4. Copy the **Bot Token** (looks like `1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ`)

### 2. Get Your Chat ID

**For a Channel:**
1. Create a new channel in Telegram
2. Add your bot as an administrator to the channel
3. Send a message to the channel
4. Visit this URL in your browser (replace `YOUR_BOT_TOKEN` with your actual token):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
5. Look for `"chat":{"id":` in the response - this is your Chat ID (will be negative for channels)

**For a Group:**
1. Create a new group in Telegram
2. Add your bot to the group
3. Send a message to the group
4. Visit the same URL as above
5. Find the Chat ID in the response

**Quick method:**
1. Add your bot to the channel/group
2. Forward a message from the channel/group to `@userinfobot`
3. It will show you the Chat ID

### 3. Configure Environment Variables

Add these to your `.env` file:

```env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ
TELEGRAM_CHAT_ID=-1001234567890
```

- **TELEGRAM_BOT_TOKEN**: Your bot token from BotFather
- **TELEGRAM_CHAT_ID**: The chat ID from step 2 (can be positive or negative number)

### 4. Configure Queue Worker

Since notifications are queued, you need a queue worker running:

```bash
# For development
php artisan queue:work

# For production (add to supervisor or systemd)
php artisan queue:work --tries=3
```

Or use the dev command that includes the queue worker:
```bash
composer dev
```

## Current Notifications

### New Post Created

Triggered when a new post is published (not drafts).

**Message Format:**
```
🆕 新帖子发布

标题: [Post Title]
作者: [Author Name]
分类: [Category Name]
摘要: [First 100 characters]...

[查看完整内容](post_url)
```

**Includes:**
- Inline button to view the post
- Direct link to the post

## How It Works

1. When a post is published, the `NewPostCreated` notification is triggered
2. The notification is queued (won't block the request)
3. Queue worker picks up the notification
4. Message is sent to your Telegram channel/group via the Telegram Bot API
5. Users in your channel/group see the notification instantly

## Adding More Notifications

### 1. Create a New Notification Class

```bash
php artisan make:notification NewPaymentReceived
```

### 2. Update the Notification Class

```php
<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewPaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $message = "💰 *新支付到账*\n\n";
        $message .= "*金额:* ¥{$this->payment->amount}\n";
        $message .= "*用户:* {$this->payment->user->name}\n";
        $message .= "*支付方式:* {$this->payment->getMethodText()}\n";

        return TelegramMessage::create()
            ->to(config('services.telegram.chat_id'))
            ->content($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
        ];
    }
}
```

### 3. Trigger the Notification

```php
use App\Services\TelegramNotifiable;
use App\Notifications\NewPaymentReceived;

// In your controller or wherever the event occurs
$telegramNotifiable = new TelegramNotifiable();
$telegramNotifiable->notify(new NewPaymentReceived($payment));
```

## Message Formatting

Telegram supports Markdown formatting:

- `*bold text*` → **bold text**
- `_italic text_` → *italic text*
- `[link text](URL)` → clickable link
- `` `inline code` `` → `inline code`
- Use `\n` for new lines

## Testing

### Test Notification Manually

You can test notifications in Tinker:

```bash
php artisan tinker
```

```php
$post = App\Models\Post::published()->first();
$telegramNotifiable = new App\Services\TelegramNotifiable();
$telegramNotifiable->notify(new App\Notifications\NewPostCreated($post));
```

Check your Telegram channel/group - you should receive a message!

## Troubleshooting

### No Messages Received

1. **Check Bot Token**: Make sure `TELEGRAM_BOT_TOKEN` is correct
2. **Check Chat ID**: Make sure `TELEGRAM_CHAT_ID` is correct (including the minus sign for channels/groups)
3. **Check Bot Permissions**: Make sure your bot is an administrator in the channel/group
4. **Check Queue Worker**: Make sure the queue worker is running
5. **Check Logs**: Look in `storage/logs/laravel.log` for errors

### Messages Not Formatting Correctly

- Make sure you're using proper Markdown syntax
- Escape special characters if needed
- Use `\n` for line breaks

### Queue Not Processing

```bash
# Check if queue jobs exist
php artisan queue:monitor

# Process queue manually
php artisan queue:work --once

# Clear failed jobs
php artisan queue:flush
```

## Available Message Features

The Telegram package supports:

- **Buttons**: Inline and keyboard buttons
- **Images**: Send photos with messages
- **Files**: Send documents
- **Location**: Send location data
- **Formatting**: Bold, italic, code, links

Example with image:

```php
return TelegramMessage::create()
    ->to(config('services.telegram.chat_id'))
    ->content($message)
    ->file($imagePath, 'photo') // Send photo
    ->button('View Post', $url);
```

## Security Notes

- Never commit your Bot Token to version control
- Use environment variables only
- Restrict bot permissions in Telegram
- Consider limiting who can trigger notifications

## Documentation

- [Laravel Telegram Notifications](https://github.com/laravel-notification-channels/telegram)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [Laravel Notifications](https://laravel.com/docs/notifications)

---

*Last updated: 2025-10-23*
