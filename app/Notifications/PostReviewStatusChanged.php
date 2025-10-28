<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class PostReviewStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $action;
    protected $reviewer;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post, string $action, $reviewer, ?string $notes = null)
    {
        $this->post = $post;
        $this->action = $action;
        $this->reviewer = $reviewer;
        $this->notes = $notes;
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
    public function toTelegram($notifiable)
    {
        $actionEmoji = match($this->action) {
            'approved' => '✅',
            'rejected' => '❌',
            'reset' => '🔄',
            default => 'ℹ️',
        };

        $actionText = match($this->action) {
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'reset' => '重置为待审核',
            default => '状态已更改',
        };

        $message = "$actionEmoji **帖子审核状态更新**\n\n";
        $message .= "📝 **帖子**: {$this->post->title}\n";
        $message .= "👤 **作者**: {$this->post->user->name}\n";
        $message .= "📂 **分类**: {$this->post->category->name}\n";
        $message .= "🎯 **操作**: {$actionText}\n";
        $message .= "👨‍💼 **审核员**: {$this->reviewer->name}\n";

        if ($this->notes) {
            $message .= "📋 **备注**: {$this->notes}\n";
        }

        $message .= "\n🔗 [查看详情](" . config('app.url') . "/admin/post-reviews/{$this->post->id})";

        return TelegramMessage::create()
            ->content($message)
            ->options([
                'parse_mode' => 'Markdown',
            ]);
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
            'action' => $this->action,
            'reviewer_id' => $this->reviewer->id,
            'reviewer_name' => $this->reviewer->name,
            'notes' => $this->notes,
        ];
    }
}
