<?php

namespace App\Http\Controllers;

use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Get featured posts (recipes)
        $featuredPosts = Post::with(['user.creatorProfile', 'category'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit(6)
            ->get();

        // Get recent posts (recipes)
        $recentPosts = Post::with(['user.creatorProfile', 'category'])
            ->published()
            ->latest('published_at')
            ->limit(8)
            ->get();

        // Get featured creators (chefs)
        $featuredCreators = CreatorProfile::with('user')
            ->where('verification_status', 'verified')
            ->where('is_featured', true)
            ->orderBy('rating', 'desc')
            ->limit(6)
            ->get();

        // Get popular categories (cuisines)
        $popularCategories = PostCategory::where('is_nav_item', false)
            ->withCount(['posts' => function($query) {
                $query->published();
            }])
            ->orderBy('posts_count', 'desc')
            ->limit(8)
            ->get();

        // Get community stats
        $stats = [
            'total_recipes' => Post::published()->count(),
            'total_chefs' => CreatorProfile::where('verification_status', 'verified')->count(),
            'total_cuisines' => PostCategory::count(),
            'total_members' => User::count(),
        ];

        // Get testimonials (top rated posts as testimonials)
        $testimonials = Post::with(['user.creatorProfile'])
            ->published()
            ->where('like_count', '>', 10)
            ->orderByDesc('like_count')
            ->limit(3)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->excerpt,
                    'author' => $post->user->creatorProfile->display_name ?? $post->user->name,
                    'rating' => min(5, ceil($post->like_count / 10)), // Convert likes to rating
                    'specialty' => $post->user->creatorProfile->specialty ?? 'Home Cook',
                ];
            });

        return Inertia::render('Home', [
            'featuredPosts' => $featuredPosts,
            'recentPosts' => $recentPosts,
            'featuredCreators' => $featuredCreators,
            'popularCategories' => $popularCategories,
            'stats' => $stats,
            'testimonials' => $testimonials,
        ]);
    }
}