<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the creator profile associated with the user.
     */
    public function creatorProfile()
    {
        return $this->hasOne(CreatorProfile::class);
    }

    /**
     * Get the user's points record.
     */
    public function points()
    {
        return $this->hasOne(UserPoints::class);
    }

    /**
     * Get all posts created by this user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all favorites by this user.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get all achievements earned by this user.
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')->withTimestamps()->withPivot('earned_at');
    }

    /**
     * Get all posts liked by this user.
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * Get all subscriptions by this user.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'subscriber_id');
    }

    /**
     * Get all creators this user is following.
     */
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Get all users following this user's creator profile.
     */
    public function followers()
    {
        return $this->hasManyThrough(
            Follow::class,
            CreatorProfile::class,
            'user_id', // Foreign key on creator_profiles table
            'creator_id', // Foreign key on follows table
            'id', // Local key on users table
            'id' // Local key on creator_profiles table
        );
    }
}
