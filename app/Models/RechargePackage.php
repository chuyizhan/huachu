<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargePackage extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'bonus',
        'description',
        'sort_order',
        'is_active',
        'is_popular',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    /**
     * Get total credits (amount + bonus)
     */
    public function getTotalAttribute(): float
    {
        return (float) ($this->amount + $this->bonus);
    }

    /**
     * Scope for active packages only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('amount');
    }
}
