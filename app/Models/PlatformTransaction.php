<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_purchase_id',
        'post_id',
        'creator_id',
        'amount',
        'percentage',
        'creator_amount',
        'total_transaction',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
            'creator_amount' => 'decimal:2',
            'total_transaction' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the post purchase that this transaction belongs to.
     */
    public function postPurchase()
    {
        return $this->belongsTo(PostPurchase::class);
    }

    /**
     * Get the post that this transaction is for.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the creator who receives payment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
