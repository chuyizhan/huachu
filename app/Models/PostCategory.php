<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'icon_image',
        'category_image',
        'sort_order',
        'is_active',
        'is_nav_item',
        'nav_route',
        'points_reward',
        'minimum_images',
        'allowed_user_types',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_nav_item' => 'boolean',
            'points_reward' => 'integer',
            'minimum_images' => 'integer',
            'allowed_user_types' => 'array',
        ];
    }

    /**
     * Get all posts in this category.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a user can post to this category.
     */
    public function canUserPost(User $user): bool
    {
        // If no restrictions set or 'all' is in allowed types, everyone can post
        $allowedTypes = $this->allowed_user_types ?? ['all'];

        if (in_array('all', $allowedTypes)) {
            return true;
        }

        // Admin can always post to any category
        if ($user->is_admin) {
            return true;
        }

        // Check if admin-only
        if (in_array('admin', $allowedTypes) && !$user->is_admin) {
            return false;
        }

        // Check creator restriction
        if (in_array('creator', $allowedTypes) && !$user->is_creator) {
            return false;
        }

        // Check regular user restriction (non-creator)
        if (in_array('regular', $allowedTypes) && $user->is_creator) {
            return false;
        }

        return true;
    }

    /**
     * Scope to get categories a user can post to.
     */
    public function scopeAllowedForUser($query, User $user)
    {
        // Admin can post to all categories
        if ($user->is_admin) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Categories with 'all' allowed
            $q->whereJsonContains('allowed_user_types', 'all')
                // Categories where creator is allowed and user is creator
                ->orWhere(function ($subQ) use ($user) {
                    if ($user->is_creator) {
                        $subQ->whereJsonContains('allowed_user_types', 'creator');
                    }
                })
                // Categories where regular is allowed and user is not creator
                ->orWhere(function ($subQ) use ($user) {
                    if (!$user->is_creator) {
                        $subQ->whereJsonContains('allowed_user_types', 'regular');
                    }
                });
        });
    }
}