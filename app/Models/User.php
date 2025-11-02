<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserSex;
use App\Enums\UserStatus;
use App\Enums\UserType;
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
        'parent_id',
        // 'is_admin',
        'is_creator',
        'is_verified',
        'is_top_creator',
        'type',
        'status',
        'login_name',
        'sex',
        'date_birth',
        'phone',
        'phone_verified_at',
        'xp',
        'points',
        'credits',
        'balance',
        'followers_count',
        'following_count',
        'avatar',
        'description',
        'last_login_ip',
        'last_login_at',
        'last_login_user_agent',
        'referral_code',
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
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_birth' => 'date',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_creator' => 'boolean',
            'is_verified' => 'boolean',
            'is_top_creator' => 'boolean',
            'type' => UserType::class,
            'status' => UserStatus::class,
            'sex' => UserSex::class,
            'xp' => 'integer',
            'points' => 'integer',
            'followers_count' => 'integer',
            'following_count' => 'integer',
            'credits' => 'decimal:2',
            'balance' => 'decimal:2',
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
     * Get all post purchases by this user.
     */
    public function postPurchases()
    {
        return $this->hasMany(PostPurchase::class);
    }

    /**
     * Get all posts purchased by this user.
     */
    public function purchasedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_purchases')->withTimestamps()->withPivot('price_paid');
    }

    /**
     * Get all subscriptions by this user (as subscriber).
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'subscriber_id');
    }

    /**
     * Get all subscriptions received by this user (as creator).
     */
    public function receivedSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'creator_id');
    }

    /**
     * Get all plan subscriptions for this user.
     */
    public function planSubscriptions()
    {
        return $this->hasMany(PlanSubscription::class);
    }

    /**
     * Get the active plan subscription for this user.
     */
    public function activePlanSubscription()
    {
        return $this->hasOne(PlanSubscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest();
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

    /**
     * Get the user who referred this user (parent in referral tree).
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get all users referred by this user (children in referral tree).
     */
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Get all point transactions for this user.
     */
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Get all credit transactions for this user.
     */
    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Get all platform transactions where this user is the creator.
     */
    public function platformTransactions()
    {
        return $this->hasMany(PlatformTransaction::class, 'creator_id');
    }

    /**
     * Get all orders for the user
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if user is a creator.
     */
    public function isCreator(): bool
    {
        return $this->is_creator;
    }

    /**
     * Check if user is verified.
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Increment followers count.
     */
    public function incrementFollowers(): void
    {
        $this->increment('followers_count');
    }

    /**
     * Decrement followers count.
     */
    public function decrementFollowers(): void
    {
        $this->decrement('followers_count');
    }

    /**
     * Increment following count.
     */
    public function incrementFollowing(): void
    {
        $this->increment('following_count');
    }

    /**
     * Decrement following count.
     */
    public function decrementFollowing(): void
    {
        $this->decrement('following_count');
    }

    /**
     * Award points to the user and create a transaction record.
     *
     * @param int $points Amount of points to award
     * @param string $reason Reason for awarding points
     * @param \Illuminate\Database\Eloquent\Model|null $related Related model (e.g., Post)
     * @param array $metadata Additional metadata
     * @return \App\Models\PointTransaction
     */
    public function awardPoints(int $points, string $reason, $related = null, array $metadata = []): PointTransaction
    {
        // Update user's points
        $this->increment('points', $points);

        // Create transaction record
        $transaction = $this->pointTransactions()->create([
            'type' => 'earned',
            'points' => $points,
            'reason' => $reason,
            'metadata' => $metadata,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
        ]);

        return $transaction;
    }
}
