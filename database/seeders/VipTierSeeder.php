<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class VipTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'level' => 1,
                'name' => 'Creator Monthly',
                'slug' => 'creator-monthly',
                'description' => 'Perfect for aspiring creators ready to take their content to the next level',
                'price' => 9.99,
                'period_days' => 30,
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
                'is_active' => true,
            ],
            [
                'level' => 1,
                'name' => 'Creator Yearly',
                'slug' => 'creator-yearly',
                'description' => 'Perfect for aspiring creators - save with yearly billing',
                'price' => 99.99,
                'period_days' => 365,
                'features' => [
                    'Create premium content',
                    'Up to 10 premium posts per month',
                    'Creator verification badge',
                    'Priority support',
                    'Advanced analytics',
                    'Custom profile themes',
                    'Save 17% with yearly billing'
                ],
                'max_premium_posts' => 10,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#10b981',
                'badge_text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'level' => 2,
                'name' => 'Pro Creator Monthly',
                'slug' => 'pro-creator-monthly',
                'description' => 'For professional creators who want unlimited potential',
                'price' => 19.99,
                'period_days' => 30,
                'features' => [
                    'All Creator features',
                    'Unlimited premium posts',
                    'Advanced analytics dashboard',
                    'Featured creator badge',
                    '24/7 priority support',
                    'Custom branding options',
                    'Early access to new features',
                    'Revenue sharing bonus'
                ],
                'max_premium_posts' => null,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#f59e0b',
                'badge_text_color' => '#000000',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'level' => 2,
                'name' => 'Pro Creator Yearly',
                'slug' => 'pro-creator-yearly',
                'description' => 'For professional creators - save with yearly billing',
                'price' => 199.99,
                'period_days' => 365,
                'features' => [
                    'All Creator features',
                    'Unlimited premium posts',
                    'Advanced analytics dashboard',
                    'Featured creator badge',
                    '24/7 priority support',
                    'Custom branding options',
                    'Early access to new features',
                    'Revenue sharing bonus',
                    'Save 17% with yearly billing'
                ],
                'max_premium_posts' => null,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#f59e0b',
                'badge_text_color' => '#000000',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'level' => 0,
                'name' => 'Premium Member Monthly',
                'slug' => 'premium-member-monthly',
                'description' => 'For users who want the best experience',
                'price' => 4.99,
                'period_days' => 30,
                'features' => [
                    'Access to all premium content',
                    'Ad-free experience',
                    'Exclusive community access',
                    'Early access to new features',
                    'Priority customer support',
                    'Special member badge'
                ],
                'max_premium_posts' => 0,
                'can_create_premium_content' => false,
                'priority_support' => true,
                'badge_color' => '#8b5cf6',
                'badge_text_color' => '#ffffff',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'level' => 0,
                'name' => 'Premium Member Yearly',
                'slug' => 'premium-member-yearly',
                'description' => 'For users who want the best experience - save with yearly billing',
                'price' => 49.99,
                'period_days' => 365,
                'features' => [
                    'Access to all premium content',
                    'Ad-free experience',
                    'Exclusive community access',
                    'Early access to new features',
                    'Priority customer support',
                    'Special member badge',
                    'Save 17% with yearly billing'
                ],
                'max_premium_posts' => 0,
                'can_create_premium_content' => false,
                'priority_support' => true,
                'badge_color' => '#8b5cf6',
                'badge_text_color' => '#ffffff',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
