<?php

namespace Database\Factories;

use App\Models\CreatorSubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreatorSubscriptionPlan>
 */
class CreatorSubscriptionPlanFactory extends Factory
{
    protected $model = CreatorSubscriptionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Common plan durations and their typical pricing
        $planOptions = [
            ['duration' => 7, 'name' => '周订阅', 'base_price' => 29.9],
            ['duration' => 30, 'name' => '月订阅', 'base_price' => 99.9],
            ['duration' => 90, 'name' => '季订阅', 'base_price' => 249.9],
            ['duration' => 365, 'name' => '年订阅', 'base_price' => 899.9],
        ];

        $selected = fake()->randomElement($planOptions);

        return [
            'creator_id' => User::factory(),
            'name' => $selected['name'],
            'description' => fake()->optional(0.7)->sentence(),
            'duration_days' => $selected['duration'],
            'price' => $selected['base_price'] * fake()->randomFloat(2, 0.8, 1.5), // Vary price by ±20-50%
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Create a weekly plan.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '周订阅',
            'duration_days' => 7,
            'price' => fake()->randomFloat(2, 19.9, 49.9),
            'description' => '体验一周创作者的专属内容',
        ]);
    }

    /**
     * Create a monthly plan.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '月订阅',
            'duration_days' => 30,
            'price' => fake()->randomFloat(2, 79.9, 149.9),
            'description' => '享受一个月的完整创作者体验',
        ]);
    }

    /**
     * Create a quarterly plan.
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '季订阅',
            'duration_days' => 90,
            'price' => fake()->randomFloat(2, 199.9, 299.9),
            'description' => '三个月深度支持，享受更多优惠',
        ]);
    }

    /**
     * Create a yearly plan.
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '年订阅',
            'duration_days' => 365,
            'price' => fake()->randomFloat(2, 699.9, 999.9),
            'description' => '全年畅享，最超值的选择',
        ]);
    }

    /**
     * Mark the plan as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
