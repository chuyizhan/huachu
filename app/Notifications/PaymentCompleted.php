<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected ?Payment $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, ?Payment $payment = null)
    {
        $this->order = $order;
        $this->payment = $payment;
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
        $message = "✅ *支付成功*\n\n";
        $message .= "*订单号:* {$this->order->order_number}\n";
        $message .= "*用户:* {$this->order->user->name}\n";
        $message .= "*金额:* ¥" . number_format($this->order->amount, 2) . "\n";
        $message .= "*类型:* " . $this->getOrderTypeText() . "\n";
        $message .= "*支付方式:* " . $this->getPaymentMethodText() . "\n";

        // Add payment details if available
        if ($this->payment) {
            if ($this->payment->trade_number) {
                $message .= "*交易号:* {$this->payment->trade_number}\n";
            }
            if ($this->payment->paid_at) {
                $message .= "*支付时间:* " . $this->payment->paid_at->format('Y-m-d H:i:s') . "\n";
            }
        }

        // Add recharge details
        if ($this->order->type === 'recharge') {
            $paymentInfo = is_array($this->order->payment_info) ? $this->order->payment_info : [];
            $baseAmount = $this->order->amount;
            $bonus = $paymentInfo['bonus_credits'] ?? 0;
            $total = $baseAmount + $bonus;

            $message .= "\n*充值详情:*\n";
            $message .= "• 基础金币: " . number_format($baseAmount, 2) . "\n";
            if ($bonus > 0) {
                $message .= "• 赠送金币: +" . number_format($bonus, 2) . "\n";
            }
            $message .= "• 总计到账: " . number_format($total, 2) . " 金币\n";
        }

        // Add subscription details
        if ($this->order->type === 'subscription') {
            $message .= "\n*订阅已激活* 🎉\n";
        }

        $message .= "\n🎊 交易完成！";

        return TelegramMessage::create()
            ->token(config('services.telegram.bot_token'))
            ->to(config('services.telegram.chat_id'))
            ->content($message);
    }

    /**
     * Get order type text
     */
    protected function getOrderTypeText(): string
    {
        return match($this->order->type) {
            'recharge' => '充值',
            'subscription' => 'VIP订阅',
            'post_purchase' => '内容购买',
            default => $this->order->type,
        };
    }

    /**
     * Get payment method text
     */
    protected function getPaymentMethodText(): string
    {
        return match($this->order->payment_method) {
            'alipay' => '支付宝',
            'wechat' => '微信支付',
            'fake' => '测试支付',
            default => $this->order->payment_method ?? '未知',
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->amount,
            'type' => $this->order->type,
            'payment_id' => $this->payment?->id,
        ];
    }
}
