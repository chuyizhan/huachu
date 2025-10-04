<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class ChinesePostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories
        PostCategory::truncate();

        $categories = [
            [
                'name' => '首页',
                'slug' => 'home',
                'description' => '社区首页和热门内容',
                'color' => '#ff6e02',
                'icon' => '🏠',
                'sort_order' => 1,
                'is_nav_item' => true,
                'nav_route' => '/',
            ],
            [
                'name' => '大厨献菜',
                'slug' => 'chef-special',
                'description' => '大厨推荐的精品菜谱',
                'color' => '#e74c3c',
                'icon' => '👨‍🍳',
                'sort_order' => 2,
                'is_nav_item' => true,
                'nav_route' => '/community/posts?category=chef-special',
            ],
            [
                'name' => '网友技术珍藏区',
                'slug' => 'user-techniques',
                'description' => '用户分享的烹饪技术和心得',
                'color' => '#3498db',
                'icon' => '📚',
                'sort_order' => 3,
                'is_nav_item' => true,
                'nav_route' => '/community/posts?category=user-techniques',
            ],
            [
                'name' => '特色小吃区',
                'slug' => 'specialty-snacks',
                'description' => '各地特色小吃制作方法',
                'color' => '#f39c12',
                'icon' => '🍡',
                'sort_order' => 4,
                'is_nav_item' => true,
                'nav_route' => '/community/posts?category=specialty-snacks',
            ],
            [
                'name' => '家常菜',
                'slug' => 'home-cooking',
                'description' => '日常家常菜谱分享',
                'color' => '#27ae60',
                'icon' => '🍽️',
                'sort_order' => 5,
                'is_nav_item' => true,
                'nav_route' => '/community/posts?category=home-cooking',
            ],
            [
                'name' => '火锅区',
                'slug' => 'hotpot',
                'description' => '火锅底料、配菜和吃法',
                'color' => '#e67e22',
                'icon' => '🍲',
                'sort_order' => 6,
                'is_nav_item' => true,
                'nav_route' => '/community/posts?category=hotpot',
            ],
            [
                'name' => '川菜',
                'slug' => 'sichuan',
                'description' => '四川菜系经典菜品',
                'color' => '#c0392b',
                'icon' => '🌶️',
                'sort_order' => 7,
                'is_nav_item' => false,
            ],
            [
                'name' => '粤菜',
                'slug' => 'cantonese',
                'description' => '广东菜系和茶点',
                'color' => '#8e44ad',
                'icon' => '🦐',
                'sort_order' => 8,
                'is_nav_item' => false,
            ],
            [
                'name' => '湘菜',
                'slug' => 'hunan',
                'description' => '湖南菜系特色菜品',
                'color' => '#d35400',
                'icon' => '🌶️',
                'sort_order' => 9,
                'is_nav_item' => false,
            ],
            [
                'name' => '面食',
                'slug' => 'noodles',
                'description' => '各类面条、饺子、包子',
                'color' => '#f1c40f',
                'icon' => '🍜',
                'sort_order' => 10,
                'is_nav_item' => false,
            ],
            [
                'name' => '烘焙甜品',
                'slug' => 'baking-desserts',
                'description' => '烘焙和甜品制作',
                'color' => '#e91e63',
                'icon' => '🧁',
                'sort_order' => 11,
                'is_nav_item' => false,
            ],
            [
                'name' => '素食',
                'slug' => 'vegetarian',
                'description' => '素食菜谱和健康饮食',
                'color' => '#4caf50',
                'icon' => '🥬',
                'sort_order' => 12,
                'is_nav_item' => false,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::create($category);
        }
    }
}