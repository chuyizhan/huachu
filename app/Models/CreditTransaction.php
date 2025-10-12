<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'credits',
        'reason',
        'metadata',
        'related_type',
        'related_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'credits' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
