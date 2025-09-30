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
        'is_featured',
        'is_premium',
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
            'published_at' => 'datetime',
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
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');
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