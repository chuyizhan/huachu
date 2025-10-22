<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'payment_number',
        'trade_number',
        'gateway',
        'payment_method',
        'amount',
        'fee',
        'actual_amount',
        'status',
        'currency',
        'payer_ip',
        'payer_name',
        'payer_account',
        'request_data',
        'response_data',
        'callback_data',
        'paid_at',
        'refunded_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'request_data' => 'array',
            'response_data' => 'array',
            'callback_data' => 'array',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'PAY' . date('YmdHis') . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Get the user who made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to filter pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter by payment method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(array $callbackData = []): bool
    {
        $this->status = 'completed';
        $this->paid_at = now();
        if (!empty($callbackData)) {
            $this->callback_data = $callbackData;
        }
        return $this->save();
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(array $callbackData = []): bool
    {
        $this->status = 'failed';
        if (!empty($callbackData)) {
            $this->callback_data = $callbackData;
        }
        return $this->save();
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(): bool
    {
        $this->status = 'refunded';
        $this->refunded_at = now();
        return $this->save();
    }

    /**
     * Mark payment as cancelled.
     */
    public function markAsCancelled(): bool
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        return $this->save();
    }

    /**
     * Get payment status text.
     */
    public function getStatusText(): string
    {
        $statusMap = [
            'pending' => '待支付',
            'processing' => '处理中',
            'completed' => '已完成',
            'failed' => '失败',
            'refunded' => '已退款',
            'cancelled' => '已取消',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Get payment method text.
     */
    public function getMethodText(): string
    {
        $methodMap = [
            'alipay' => '支付宝',
            'wechat' => '微信支付',
            'bank' => '网银',
            'fake' => '测试支付',
        ];

        return $methodMap[$this->payment_method] ?? $this->payment_method;
    }
}
