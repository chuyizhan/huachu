<?php

namespace Database\Factories;

use App\Enums\UserSex;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Referral system
            'parent_id' => null,

            // User roles and status
            'is_admin' => false,
            'is_creator' => false,
            'is_verified' => false,
            'is_top_creator' => false,
            'type' => UserType::REGULAR,
            'status' => UserStatus::ACTIVE,

            // Login and profile
            'login_name' => $this->faker->unique()->userName(),
            'sex' => $this->faker->randomElement([UserSex::MALE, UserSex::FEMALE, UserSex::OTHER]),
            'date_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'phone' => $this->faker->optional(0.7)->numerify('1##########'),
            'phone_verified_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 year', 'now'),

                // Gamification and economy
            'xp' => $this->faker->numberBetween(0, 10000),
            'points' => $this->faker->numberBetween(0, 5000),
            'credits' => $this->faker->randomFloat(2, 0, 1000),
            'balance' => $this->faker->randomFloat(2, 0, 500),

            // Social metrics
            'followers_count' => $this->faker->numberBetween(0, 1000),
            'following_count' => $this->faker->numberBetween(0, 500),

            // Profile details
            'avatar' => $this->faker->optional(0.6)->imageUrl(200, 200, 'people'),
            'description' => $this->faker->optional(0.5)->sentence(20),
            'signature' => $this->faker->optional(0.5)->sentence(20),
            // Login tracking
            'last_login_ip' => $this->faker->optional(0.8)->ipv4(),
            'last_login_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'last_login_user_agent' => $this->faker->optional(0.8)->userAgent(),

            // Referral code
            'referral_code' => strtoupper(Str::random(8)),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the user is a creator.
     */
    public function creator(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_creator' => true,
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the user is a verified creator.
     */
    public function verifiedCreator(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_creator' => true,
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the user is a top creator.
     */
    public function topCreator(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_creator' => true,
            'is_verified' => true,
            'is_top_creator' => true,
            'followers_count' => $this->faker->numberBetween(1000, 50000),
            'xp' => $this->faker->numberBetween(5000, 50000),
        ]);
    }

    /**
     * Indicate that the user is a premium user.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::PREMIUM,
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::SUSPENDED,
        ]);
    }

    /**
     * Indicate that the user is banned.
     */
    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::BANNED,
        ]);
    }

    /**
     * Indicate that the user has a parent (was referred).
     */
    public function withParent(User $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Indicate that the user has high engagement.
     */
    public function highEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'xp' => $this->faker->numberBetween(10000, 100000),
            'points' => $this->faker->numberBetween(5000, 50000),
            'followers_count' => $this->faker->numberBetween(500, 10000),
            'following_count' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    /**
     * Indicate that the user is new (low engagement).
     */
    public function newUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'xp' => $this->faker->numberBetween(0, 100),
            'points' => $this->faker->numberBetween(0, 50),
            'followers_count' => $this->faker->numberBetween(0, 10),
            'following_count' => $this->faker->numberBetween(0, 20),
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
