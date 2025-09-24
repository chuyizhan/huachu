<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorProfile extends Model
{
    use HasFactory;

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
    ];

    protected function casts(): array
    {
        return [
            'certifications' => 'array',
            'social_links' => 'array',
            'verified_at' => 'datetime',
            'is_featured' => 'boolean',
            'rating' => 'decimal:2',
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
     * Get all subscribers of this creator.
     */
    public function subscribers()
    {
        return $this->hasMany(UserSubscription::class, 'creator_id');
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