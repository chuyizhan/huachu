<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General Discussion',
                'slug' => 'general-discussion',
                'description' => 'General community discussions and conversations',
                'color' => '#3b82f6',
                'icon' => 'chat-bubble-left-right',
                'sort_order' => 1,
            ],
            [
                'name' => 'Tutorials & Guides',
                'slug' => 'tutorials-guides',
                'description' => 'Step-by-step tutorials and helpful guides',
                'color' => '#10b981',
                'icon' => 'academic-cap',
                'sort_order' => 2,
            ],
            [
                'name' => 'Showcase',
                'slug' => 'showcase',
                'description' => 'Show off your latest work and creations',
                'color' => '#f59e0b',
                'icon' => 'star',
                'sort_order' => 3,
            ],
            [
                'name' => 'Q&A Help',
                'slug' => 'qa-help',
                'description' => 'Ask questions and get help from the community',
                'color' => '#ef4444',
                'icon' => 'question-mark-circle',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tips & Tricks',
                'slug' => 'tips-tricks',
                'description' => 'Quick tips and clever tricks to share',
                'color' => '#8b5cf6',
                'icon' => 'light-bulb',
                'sort_order' => 5,
            ],
            [
                'name' => 'Reviews',
                'slug' => 'reviews',
                'description' => 'Product and service reviews',
                'color' => '#06b6d4',
                'icon' => 'chart-bar',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::create($category);
        }
    }
}