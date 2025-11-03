<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CreatorSubscriptionPlan;
use Illuminate\Database\Seeder;

class CreatorSubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all creators
        $creators = User::where('is_creator', true)->get();

        if ($creators->isEmpty()) {
            $this->command->warn('No creators found. Please create creators first.');
            return;
        }

        $this->command->info("Creating subscription plans for {$creators->count()} creators...");

        foreach ($creators as $creator) {
            // Randomly decide how many plans this creator has (1-3)
            $planCount = rand(1, 3);

            // Delete existing plans for this creator
            CreatorSubscriptionPlan::where('creator_id', $creator->id)->delete();

            // Common plan templates
            $planTemplates = [
                [
                    'name' => '周订阅',
                    'duration_days' => 7,
                    'price' => rand(29, 59),
                    'description' => '体验一周创作者的专属内容',
                    'sort_order' => 0,
                ],
                [
                    'name' => '月订阅',
                    'duration_days' => 30,
                    'price' => rand(99, 149),
                    'description' => '享受一个月的完整创作者体验',
                    'sort_order' => 1,
                ],
                [
                    'name' => '季订阅',
                    'duration_days' => 90,
                    'price' => rand(249, 349),
                    'description' => '三个月深度支持，享受更多优惠',
                    'sort_order' => 2,
                ],
            ];

            // Create plans based on count
            $selectedPlans = array_slice($planTemplates, 0, $planCount);

            foreach ($selectedPlans as $planData) {
                CreatorSubscriptionPlan::create([
                    'creator_id' => $creator->id,
                    'name' => $planData['name'],
                    'description' => $planData['description'],
                    'duration_days' => $planData['duration_days'],
                    'price' => $planData['price'],
                    'is_active' => true,
                    'sort_order' => $planData['sort_order'],
                ]);
            }

            $this->command->info("  ✓ Created {$planCount} plans for {$creator->name}");
        }

        $totalPlans = CreatorSubscriptionPlan::count();
        $this->command->info("✓ Total subscription plans created: {$totalPlans}");
    }
}
