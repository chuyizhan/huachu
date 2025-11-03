<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreatorProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'display_name',
        'bio',
        'specialty',
        'experience_years',
        'certifications',
        'location',
        'website',
        'social_links',
        'portfolio_url',
        'verification_status',
        'verified_at',
        'verification_notes',
        'is_featured',
        'follower_count',
        'following_count',
        'rating',
        'review_count',
        'platform_commission_rate',
        'total_earnings',
        'total_platform_share',
    ];

    protected function casts(): array
    {
        return [
            'certifications' => 'array',
            'social_links' => 'array',
            'verified_at' => 'datetime',
            'is_featured' => 'boolean',
            'rating' => 'decimal:2',
            'platform_commission_rate' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_platform_share' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the creator profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all subscription plans for this creator.
     */
    public function subscriptionPlans()
    {
        return $this->hasMany(CreatorSubscriptionPlan::class, 'creator_id', 'user_id');
    }

    /**
     * Get all subscribers of this creator.
     */
    public function subscribers()
    {
        return $this->hasMany(UserSubscription::class, 'creator_id');
    }

    /**
     * Get all follows for this creator.
     */
    public function follows()
    {
        return $this->hasMany(Follow::class, 'creator_id');
    }

    /**
     * Get all users following this creator.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'creator_id', 'follower_id')->withTimestamps();
    }

    /**
     * Scope for verified creators.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope for featured creators.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}