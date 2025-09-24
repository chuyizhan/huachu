<?php

namespace Database\Seeders;

use App\Models\VipTier;
use Illuminate\Database\Seeder;

class VipTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Creator',
                'slug' => 'creator',
                'description' => 'Perfect for aspiring creators ready to take their content to the next level',
                'monthly_price' => 9.99,
                'yearly_price' => 99.99,
                'features' => [
                    'Create premium content',
                    'Up to 10 premium posts per month',
                    'Creator verification badge',
                    'Priority support',
                    'Advanced analytics',
                    'Custom profile themes'
                ],
                'max_premium_posts' => 10,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#10b981',
                'badge_text_color' => '#ffffff',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Creator',
                'slug' => 'pro-creator',
                'description' => 'For established creators who want unlimited premium content and exclusive features',
                'monthly_price' => 19.99,
                'yearly_price' => 199.99,
                'features' => [
                    'All Creator features',
                    'Unlimited premium posts',
                    'Subscriber-only content',
                    'Advanced creator tools',
                    'Revenue sharing program',
                    'Monthly creator spotlight',
                    'Direct message access'
                ],
                'max_premium_posts' => null, // unlimited
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#f59e0b',
                'badge_text_color' => '#000000',
                'sort_order' => 2,
            ],
            [
                'name' => 'Community Plus',
                'slug' => 'community-plus',
                'description' => 'Enhanced community access with premium content and exclusive perks',
                'monthly_price' => 4.99,
                'yearly_price' => 49.99,
                'features' => [
                    'Access to all premium content',
                    'Ad-free browsing',
                    'Early access to new features',
                    'Exclusive community events',
                    'Priority customer support',
                    'Special member badge'
                ],
                'max_premium_posts' => 0,
                'can_create_premium_content' => false,
                'priority_support' => true,
                'badge_color' => '#8b5cf6',
                'badge_text_color' => '#ffffff',
                'sort_order' => 3,
            ],
        ];

        foreach ($tiers as $tier) {
            VipTier::create($tier);
        }
    }
}