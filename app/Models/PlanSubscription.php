<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlanSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_number',
        'status',
        'price_paid',
        'payment_method',
        'payment_transaction_id',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'last_renewed_at',
        'renewal_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_renewed_at' => 'datetime',
            'auto_renew' => 'boolean',
            'renewal_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->subscription_number)) {
                $subscription->subscription_number = 'SUB-' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Get the user who owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan associated with the subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the orders associated with this subscription.
     */
    public function orders()
    {
        return $this->morphMany(Order::class, 'related');
    }

    /**
     * Scope to filter active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to filter expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    /**
     * Scope to filter cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to filter trial subscriptions.
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '>', now());
            });
    }

    /**
     * Scope to filter subscriptions expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
               ($this->expires_at !== null && $this->expires_at->isPast());
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is on trial.
     */
    public function isTrial(): bool
    {
        return $this->status === 'trial' &&
               ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    /**
     * Check if subscription is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get days until expiration.
     */
    public function daysUntilExpiration(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(string $reason = null): bool
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->auto_renew = false;

        return $this->save();
    }

    /**
     * Suspend the subscription.
     */
    public function suspend(): bool
    {
        $this->status = 'suspended';
        return $this->save();
    }

    /**
     * Resume a suspended subscription.
     */
    public function resume(): bool
    {
        if ($this->status === 'suspended') {
            $this->status = 'active';
            return $this->save();
        }

        return false;
    }

    /**
     * Renew the subscription.
     */
    public function renew(): bool
    {
        if (!$this->plan) {
            return false;
        }

        $this->status = 'active';
        $this->last_renewed_at = now();
        $this->renewal_count++;

        // Extend expiration date by plan period
        if ($this->expires_at && $this->expires_at->isFuture()) {
            $this->expires_at = $this->expires_at->addDays($this->plan->period_days);
        } else {
            $this->expires_at = now()->addDays($this->plan->period_days);
        }

        return $this->save();
    }

    /**
     * Mark subscription as expired.
     */
    public function markAsExpired(): bool
    {
        $this->status = 'expired';
        return $this->save();
    }

    /**
     * Activate the subscription.
     */
    public function activate(): bool
    {
        $this->status = 'active';
        $this->started_at = $this->started_at ?? now();

        return $this->save();
    }
}
