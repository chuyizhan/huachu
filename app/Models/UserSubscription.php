<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_id',
        'creator_id',
        'plan_id',
        'creator_subscription_plan_id',
        'type',
        'amount',
        'platform_amount',
        'creator_amount',
        'billing_cycle',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_amount' => 'decimal:2',
            'creator_amount' => 'decimal:2',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function creatorSubscriptionPlan()
    {
        return $this->belongsTo(CreatorSubscriptionPlan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }
}