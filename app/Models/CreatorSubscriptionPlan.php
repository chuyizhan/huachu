<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorSubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'name',
        'description',
        'duration_days',
        'price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the creator who owns this plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'creator_subscription_plan_id');
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('duration_days');
    }

    /**
     * Get the price per day.
     */
    public function getPricePerDayAttribute(): float
    {
        return $this->duration_days > 0 ? $this->price / $this->duration_days : 0;
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_days == 7) {
            return '1周';
        } elseif ($this->duration_days == 30) {
            return '1个月';
        } elseif ($this->duration_days == 90) {
            return '3个月';
        } elseif ($this->duration_days == 365) {
            return '1年';
        }

        return "{$this->duration_days}天";
    }
}
