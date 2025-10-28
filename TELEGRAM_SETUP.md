# Telegram Notification Setup

This guide explains how to set up Telegram notifications for post review status changes.

## Features

When a post review status changes (approved, rejected, or reset to pending), a notification will be sent to your configured Telegram chat with the following information:

- Post title
- Author name
- Category
- Action taken (approved/rejected/reset)
- Reviewer name
- Optional notes
- Direct link to the post

## Setup Instructions

### 1. Create a Telegram Bot

1. Open Telegram and search for [@BotFather](https://t.me/botfather)
2. Send the command `/newbot`
3. Follow the instructions to create your bot
4. BotFather will give you a **bot token** - save this for later

### 2. Get Your Chat ID

#### Option A: For a Channel
1. Create a Telegram channel (if you don't have one)
2. Add your bot as an administrator to the channel
3. Post a message to the channel
4. Open this URL in your browser (replace `YOUR_BOT_TOKEN` with your actual token):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
5. Look for the `"chat":{"id":` field - this is your chat ID (it will be negative for channels, e.g., `-1001234567890`)

#### Option B: For a Private Group
1. Create a private group in Telegram
2. Add your bot to the group
3. Send a message to the group mentioning the bot (e.g., `@your_bot_name hello`)
4. Open this URL in your browser (replace `YOUR_BOT_TOKEN` with your actual token):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
5. Look for the `"chat":{"id":` field - this is your chat ID (it will be negative for groups)

#### Option C: For Personal Messages
1. Start a conversation with your bot in Telegram
2. Send any message to the bot
3. Open this URL in your browser (replace `YOUR_BOT_TOKEN` with your actual token):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
4. Look for the `"chat":{"id":` field - this is your chat ID (it will be positive for personal chats)

### 3. Configure Environment Variables

Add these values to your `.env` file:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

**Example:**
```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=-1001234567890
```

### 4. Set Up Queue Worker (Optional but Recommended)

The notifications are queued to avoid slowing down the application. Make sure your queue worker is running:

```bash
php artisan queue:work
```

For production, set up a supervisor or systemd service to keep the queue worker running.

Alternatively, you can set `QUEUE_CONNECTION=sync` in your `.env` file to send notifications synchronously (not recommended for production).

## Testing

To test if your setup works:

1. Go to the admin post review page
2. Approve, reject, or reset a post
3. Check your Telegram chat/channel - you should receive a notification

## Notification Format

The notifications include emojis for easy visual identification:

- ✅ **Approved** - Post has been approved
- ❌ **Rejected** - Post has been rejected
- 🔄 **Reset** - Post status reset to pending

Each notification includes:
- 📝 Post title
- 👤 Author name
- 📂 Category
- 🎯 Action taken
- 👨‍💼 Reviewer name
- 📋 Notes (if provided)
- 🔗 Direct link to view the post

## Troubleshooting

### Notifications not appearing?

1. **Check your .env file**: Make sure `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` are set correctly
2. **Clear config cache**: Run `php artisan config:clear`
3. **Check logs**: Look at `storage/logs/laravel.log` for any errors
4. **Verify bot permissions**: Make sure your bot is an admin in the channel/group
5. **Test the bot manually**: Send a test message using:
   ```bash
   curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/sendMessage" \
        -d "chat_id=<YOUR_CHAT_ID>" \
        -d "text=Test message"
   ```

### Queue not processing?

- Make sure the queue worker is running: `php artisan queue:work`
- Check the `jobs` table in your database to see if jobs are stuck
- Restart the queue worker: `php artisan queue:restart`

## Disabling Notifications

To disable Telegram notifications, simply remove or comment out the `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` from your `.env` file. The application will continue to work normally without sending notifications.
