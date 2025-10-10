<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'price_paid',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
        ];
    }

    /**
     * Get the user who purchased the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that was purchased.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
