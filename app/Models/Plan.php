<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'name',
        'slug',
        'description',
        'price',
        'period_days',
        'features',
        'max_premium_posts',
        'can_create_premium_content',
        'priority_support',
        'badge_color',
        'badge_text_color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'period_days' => 'integer',
            'features' => 'array',
            'price' => 'decimal:2',
            'can_create_premium_content' => 'boolean',
            'priority_support' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
