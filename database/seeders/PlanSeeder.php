<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'level' => 1,
                'name' => '基础会员',
                'slug' => 'basic',
                'description' => '开启你的美食创作之旅',
                'price' => 29.00,
                'period_days' => 30,
                'features' => [
                    '每月发布最多 5 个菜谱',
                    '基础统计数据',
                    '社区支持',
                ],
                'max_premium_posts' => 5,
                'can_create_premium_content' => false,
                'priority_support' => false,
                'badge_color' => '#60a5fa',
                'badge_text_color' => '#ffffff',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'level' => 2,
                'name' => '高级会员',
                'slug' => 'premium',
                'description' => '解锁更多创作功能和收益',
                'price' => 99.00,
                'period_days' => 30,
                'features' => [
                    '每月发布最多 20 个菜谱',
                    '可创建付费内容',
                    '高级数据分析',
                    '优先客服支持',
                    '专属会员徽章',
                ],
                'max_premium_posts' => 20,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#f59e0b',
                'badge_text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'level' => 3,
                'name' => '专业会员',
                'slug' => 'professional',
                'description' => '专业创作者的最佳选择',
                'price' => 299.00,
                'period_days' => 30,
                'features' => [
                    '无限制发布菜谱',
                    '可创建付费内容',
                    '全方位数据分析',
                    '24/7 优先客服',
                    '专属金色徽章',
                    '推广资源支持',
                    '独家活动邀请',
                ],
                'max_premium_posts' => null,
                'can_create_premium_content' => true,
                'priority_support' => true,
                'badge_color' => '#eab308',
                'badge_text_color' => '#000000',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
