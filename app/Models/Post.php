<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'post_category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'images',
        'videos',
        'attachments',
        'type',
        'status',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'is_featured',
        'is_premium',
        'price',
        'free_after',
        'view_count',
        'like_count',
        'comment_count',
        'share_count',
        'tags',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'videos' => 'array',
            'attachments' => 'array',
            'tags' => 'array',
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'price' => 'decimal:2',
            'free_after' => 'datetime',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that created the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin user who reviewed this post.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the category this post belongs to.
     */
    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * Get all likes for this post.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get users who liked this post.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    /**
     * Get all favorites for this post.
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Get all purchases for this post.
     */
    public function purchases()
    {
        return $this->hasMany(PostPurchase::class);
    }

    /**
     * Get users who purchased this post.
     */
    public function purchasedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_purchases')->withTimestamps()->withPivot('price_paid');
    }

    /**
     * Get all comments for the post
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->topLevel()->with(['user', 'replies'])->latest();
    }

    /**
     * Get comments count
     */
    public function commentsCount()
    {
        return $this->morphMany(Comment::class, 'commentable')->count();
    }

    /**
     * Check if a user has purchased this post.
     */
    public function isPurchasedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->purchases()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user can view this post (free or subscribed to creator).
     */
    public function canBeViewedBy(?User $user): bool
    {
        // User is the author - always can view
        if ($user && $user->id === $this->user_id) {
            return true;
        }

        // Post is not premium - everyone can view
        if (!$this->is_premium) {
            return true;
        }

        // Post is premium - check if user has active subscription to creator
        if (!$user) {
            return false;
        }

        // Check if user has an active subscription to this post's creator
        $hasActiveSubscription = \App\Models\UserSubscription::where('subscriber_id', $user->id)
            ->where('creator_id', $this->user_id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasActiveSubscription) {
            return true;
        }

        // Legacy: Check if user has purchased this specific post (backward compatibility)
        if ($this->isPurchasedBy($user)) {
            return true;
        }

        return false;
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    /**
     * Scope for featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for premium posts.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for posts by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for posts pending review.
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    /**
     * Scope for approved posts.
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    /**
     * Scope for rejected posts.
     */
    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }

    /**
     * Scope for posts by review status.
     */
    public function scopeReviewStatus($query, $status)
    {
        return $query->where('review_status', $status);
    }

    /**
     * Check if this post requires payment to view.
     */
    public function isPaid(): bool
    {
        return $this->price !== null && $this->price > 0;
    }

    /**
     * Check if this post is currently free (even if it has a price).
     */
    public function isFree(): bool
    {
        // Free if no price set
        if (!$this->isPaid()) {
            return true;
        }

        // Free if free_after date has passed
        if ($this->free_after && now()->greaterThanOrEqualTo($this->free_after)) {
            return true;
        }

        return false;
    }

    /**
     * Check if this post is currently locked (requires subscription).
     */
    public function isLocked(): bool
    {
        return $this->is_premium;
    }

    /**
     * Scope for free posts (no price or free_after has passed).
     */
    public function scopeFree($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('price')
              ->orWhere('price', '<=', 0)
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('free_after')
                     ->where('free_after', '<=', now());
              });
        });
    }

    /**
     * Scope for paid posts (has price and not yet free).
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('free_after')
                  ->orWhere('free_after', '>', now());
            });
    }

    /**
     * Get the disk name for media storage.
     *
     * @return string
     */
    public function mediaDisk(): string
    {
        return config('media.disk', 'public');
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk(config('media.collections.images.disk', $this->mediaDisk()))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('videos')
            ->useDisk(config('media.collections.videos.disk', $this->mediaDisk()))
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo']);
    }

    /**
     * Register media conversions for this model.
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images');

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('images');
    }

}