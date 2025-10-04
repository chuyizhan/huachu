<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'creator_id',
    ];

    /**
     * The user who is following (the follower)
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * The creator being followed
     */
    public function creator()
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }

    /**
     * Check if a user is following a specific creator
     */
    public static function isFollowing(int $followerId, int $creatorId): bool
    {
        return self::where('follower_id', $followerId)
            ->where('creator_id', $creatorId)
            ->exists();
    }

    /**
     * Get followers count for a creator
     */
    public static function getFollowersCount(int $creatorId): int
    {
        return self::where('creator_id', $creatorId)->count();
    }

    /**
     * Get following count for a user
     */
    public static function getFollowingCount(int $followerId): int
    {
        return self::where('follower_id', $followerId)->count();
    }
}
