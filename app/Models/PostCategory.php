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
        'sort_order',
        'is_active',
        'is_nav_item',
        'nav_route',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_nav_item' => 'boolean',
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
}