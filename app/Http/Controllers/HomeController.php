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
        $featuredPosts = Post::with(['user.creatorProfile', 'category', 'media'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit(6)
            ->get()
            ->map(function ($post) {
                $firstMedia = $post->getFirstMedia('images');
                if ($firstMedia) {
                    $isCloudStorage = $firstMedia->disk === 'wasabi' || $firstMedia->disk === 's3';

                    $url = $isCloudStorage
                        ? \Storage::disk($firstMedia->disk)->temporaryUrl($firstMedia->getPathRelativeToRoot(), now()->addHours(24))
                        : $firstMedia->getUrl();

                    $thumbUrl = $firstMedia->hasGeneratedConversion('thumb')
                        ? ($isCloudStorage
                            ? \Storage::disk($firstMedia->disk)->temporaryUrl($firstMedia->getPath('thumb'), now()->addHours(24))
                            : $firstMedia->getUrl('thumb'))
                        : $url;

                    $post->first_image = [
                        'url' => $url,
                        'thumb' => $thumbUrl,
                    ];
                } else {
                    $post->first_image = null;
                }
                return $post;
            });

        // Get recent posts (recipes)
        $recentPosts = Post::with(['user.creatorProfile', 'category', 'media'])
            ->published()
            ->latest('published_at')
            ->limit(8)
            ->get()
            ->map(function ($post) {
                // Get first 3 images from media library
                $post->post_images = $post->getMedia('images')->take(3)->map(function ($media) {
                    $isCloudStorage = $media->disk === 'wasabi' || $media->disk === 's3';

                    $url = $isCloudStorage
                        ? \Storage::disk($media->disk)->temporaryUrl($media->getPathRelativeToRoot(), now()->addHours(24))
                        : $media->getUrl();

                    $thumbUrl = $media->hasGeneratedConversion('thumb')
                        ? ($isCloudStorage
                            ? \Storage::disk($media->disk)->temporaryUrl($media->getPath('thumb'), now()->addHours(24))
                            : $media->getUrl('thumb'))
                        : $url;

                    return [
                        'url' => $url,
                        'thumb' => $thumbUrl,
                    ];
                })->toArray();

                // Check if post has videos
                $post->has_video = $post->getMedia('videos')->count() > 0;

                return $post;
            });

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

        // Get categories
        $categories = PostCategory::where('is_nav_item', true)
            ->get();

        return Inertia::render('Home', [
            'featuredPosts' => $featuredPosts,
            'recentPosts' => $recentPosts,
            'featuredCreators' => $featuredCreators,
            'popularCategories' => $popularCategories,
            'stats' => $stats,
            'testimonials' => $testimonials,
            'categories' => $categories,
        ]);
    }
}