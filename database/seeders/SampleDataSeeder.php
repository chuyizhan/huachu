<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users with creator profiles
        $chefs = [
            [
                'name' => 'Marco Giuseppe',
                'email' => 'marco@example.com',
                'display_name' => 'Chef Marco',
                'bio' => 'Italian cuisine specialist with 15 years of experience in traditional pasta making and authentic Mediterranean flavors.',
                'specialty' => 'Italian Cuisine',
                'location' => 'Rome, Italy',
                'experience_years' => 15,
                'rating' => 4.8,
                'follower_count' => 2340,
            ],
            [
                'name' => 'Sakura Tanaka',
                'email' => 'sakura@example.com',
                'display_name' => 'Chef Sakura',
                'bio' => 'Master of Japanese cuisine, specializing in sushi, ramen, and traditional kaiseki dining experiences.',
                'specialty' => 'Japanese Cuisine',
                'location' => 'Tokyo, Japan',
                'experience_years' => 12,
                'rating' => 4.9,
                'follower_count' => 1890,
            ],
            [
                'name' => 'Pierre Dubois',
                'email' => 'pierre@example.com',
                'display_name' => 'Chef Pierre',
                'bio' => 'French pastry chef with expertise in classic desserts, bread making, and modern patisserie techniques.',
                'specialty' => 'French Pastry',
                'location' => 'Paris, France',
                'experience_years' => 20,
                'rating' => 4.7,
                'follower_count' => 3120,
            ],
            [
                'name' => 'Maria Rodriguez',
                'email' => 'maria@example.com',
                'display_name' => 'Chef Maria',
                'bio' => 'Authentic Mexican cuisine expert, passionate about traditional family recipes and modern fusion dishes.',
                'specialty' => 'Mexican Cuisine',
                'location' => 'Mexico City, Mexico',
                'experience_years' => 8,
                'rating' => 4.6,
                'follower_count' => 1456,
            ],
        ];

        $categories = PostCategory::all();

        foreach ($chefs as $chefData) {
            // Create user
            $user = User::create([
                'name' => $chefData['name'],
                'email' => $chefData['email'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create creator profile
            $creator = CreatorProfile::create([
                'user_id' => $user->id,
                'display_name' => $chefData['display_name'],
                'bio' => $chefData['bio'],
                'specialty' => $chefData['specialty'],
                'location' => $chefData['location'],
                'experience_years' => $chefData['experience_years'],
                'rating' => $chefData['rating'],
                'follower_count' => $chefData['follower_count'],
                'verification_status' => 'verified',
                'is_featured' => true,
                'social_links' => [
                    'instagram' => 'https://instagram.com/' . strtolower(str_replace(' ', '', $chefData['name'])),
                    'website' => 'https://chef-' . strtolower(str_replace(' ', '-', $chefData['name'])) . '.com',
                ],
            ]);

            // Create sample posts for each chef
            $posts = $this->getPostsForChef($chefData['specialty']);

            foreach ($posts as $postData) {
                Post::create([
                    'user_id' => $user->id,
                    'post_category_id' => $categories->where('name', $postData['category'])->first()?->id ?? $categories->first()->id,
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']),
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'type' => $postData['type'],
                    'status' => 'published',
                    'is_featured' => $postData['is_featured'] ?? false,
                    'is_premium' => $postData['is_premium'] ?? false,
                    'view_count' => rand(100, 2000),
                    'like_count' => rand(10, 150),
                    'published_at' => now()->subDays(rand(1, 30)),
                    'tags' => $postData['tags'] ?? [],
                ]);
            }
        }
    }

    private function getPostsForChef(string $specialty): array
    {
        $posts = [
            'Italian Cuisine' => [
                [
                    'title' => 'Authentic Carbonara: The Roman Way',
                    'excerpt' => 'Learn the traditional Roman carbonara recipe with just 5 ingredients: eggs, pecorino, guanciale, black pepper, and pasta.',
                    'content' => 'Carbonara is one of Rome\'s most iconic pasta dishes. The secret lies in the technique of creating a creamy sauce without cream...',
                    'category' => 'European',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['pasta', 'italian', 'traditional', 'roman'],
                ],
                [
                    'title' => 'Homemade Gnocchi Masterclass',
                    'excerpt' => 'Master the art of making pillowy soft gnocchi from scratch with this step-by-step guide.',
                    'content' => 'Making gnocchi is an art form passed down through generations. Start with the right potatoes...',
                    'category' => 'European',
                    'type' => 'tutorial',
                    'tags' => ['gnocchi', 'handmade', 'italian', 'technique'],
                ],
            ],
            'Japanese Cuisine' => [
                [
                    'title' => 'Perfect Sushi Rice Every Time',
                    'excerpt' => 'The foundation of great sushi is perfectly seasoned rice. Learn the traditional method used by sushi masters.',
                    'content' => 'Sushi rice is the heart of sushi. The balance of rice vinegar, sugar, and salt creates the perfect flavor profile...',
                    'category' => 'Asian',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['sushi', 'rice', 'japanese', 'fundamental'],
                ],
                [
                    'title' => 'Ramen Broth: 12-Hour Tonkotsu',
                    'excerpt' => 'Create rich, creamy tonkotsu broth that rivals the best ramen shops with this authentic recipe.',
                    'content' => 'True tonkotsu ramen requires patience and technique. Start with quality pork bones and simmer for 12 hours...',
                    'category' => 'Asian',
                    'type' => 'tutorial',
                    'is_premium' => true,
                    'tags' => ['ramen', 'tonkotsu', 'broth', 'japanese'],
                ],
            ],
            'French Pastry' => [
                [
                    'title' => 'Classic French Croissants',
                    'excerpt' => 'Master the lamination technique to create buttery, flaky croissants with perfect layers.',
                    'content' => 'French croissants require precision and patience. The lamination process creates those beautiful layers...',
                    'category' => 'European',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'is_premium' => true,
                    'tags' => ['croissant', 'pastry', 'lamination', 'french'],
                ],
                [
                    'title' => 'Chocolate Soufflé Secrets',
                    'excerpt' => 'Learn the techniques to create a perfectly risen chocolate soufflé that never falls.',
                    'content' => 'A perfect soufflé is all about technique. From properly whipped egg whites to the right folding method...',
                    'category' => 'European',
                    'type' => 'tutorial',
                    'tags' => ['souffle', 'chocolate', 'dessert', 'technique'],
                ],
            ],
            'Mexican Cuisine' => [
                [
                    'title' => 'Authentic Mole Poblano',
                    'excerpt' => 'Discover the complex flavors of traditional mole poblano with this family recipe passed down for generations.',
                    'content' => 'Mole poblano is one of Mexico\'s most complex sauces, combining chocolate, chilies, and spices...',
                    'category' => 'Latin American',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['mole', 'poblano', 'mexican', 'traditional'],
                ],
                [
                    'title' => 'Fresh Corn Tortillas',
                    'excerpt' => 'Make authentic corn tortillas from scratch using masa harina and traditional techniques.',
                    'content' => 'Nothing beats fresh, warm corn tortillas made by hand. Start with quality masa harina...',
                    'category' => 'Latin American',
                    'type' => 'tutorial',
                    'tags' => ['tortillas', 'corn', 'handmade', 'mexican'],
                ],
            ],
        ];

        return $posts[$specialty] ?? [];
    }
}
