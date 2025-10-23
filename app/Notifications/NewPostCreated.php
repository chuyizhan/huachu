<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewPostCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Post $post;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
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
        $url = route('posts.show', $this->post->slug);

        $message = "🆕 *新帖子发布*\n\n";
        $message .= "*标题:* {$this->post->title}\n";
        $message .= "*作者:* {$this->post->user->name}\n";
        $message .= "*分类:* {$this->post->category->name}\n";

        if ($this->post->excerpt) {
            $message .= "*摘要:* " . mb_substr($this->post->excerpt, 0, 100) . "...\n";
        }

        $message .= "\n[查看完整内容]({$url})";

        return TelegramMessage::create()
            ->to(config('services.telegram.chat_id'))
            ->content($message)
            ->button('查看帖子', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_url' => route('posts.show', $this->post->slug),
        ];
    }
}
