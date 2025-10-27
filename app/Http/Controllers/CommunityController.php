<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\CreatorProfile;
use App\Models\UserPoints;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        // Get featured posts
        $featuredPosts = Post::with(['user', 'category', 'media'])
            ->published()
            ->featured()
            ->take(6)
            ->get()
            ->map(function ($post) {
                $firstMedia = $post->getFirstMedia('images');
                $post->first_image = $firstMedia ? [
                    'url' => $firstMedia->getUrl(),
                    'thumb' => $firstMedia->hasGeneratedConversion('thumb')
                        ? $firstMedia->getUrl('thumb')
                        : $firstMedia->getUrl(),
                ] : null;
                return $post;
            });

        // Get recent posts
        $recentPosts = Post::with(['user', 'category', 'media'])
            ->published()
            ->latest('published_at')
            ->take(12)
            ->get()
            ->map(function ($post) {
                $firstMedia = $post->getFirstMedia('images');
                $post->first_image = $firstMedia ? [
                    'url' => $firstMedia->getUrl(),
                    'thumb' => $firstMedia->hasGeneratedConversion('thumb')
                        ? $firstMedia->getUrl('thumb')
                        : $firstMedia->getUrl(),
                ] : null;
                return $post;
            });

        // Get categories with post counts
        $categories = PostCategory::active()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->get();

        // Get featured creators
        $featuredCreators = CreatorProfile::with('user')
            ->verified()
            ->featured()
            ->take(8)
            ->get();

        // Get top users by points
        $topUsers = UserPoints::with('user')
            ->orderBy('points', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Community/Index', [
            'featuredPosts' => $featuredPosts,
            'recentPosts' => $recentPosts,
            'categories' => $categories,
            'featuredCreators' => $featuredCreators,
            'topUsers' => $topUsers,
        ]);
    }

    public function posts(Request $request)
    {
        $query = Post::with(['user.creatorProfile', 'category', 'media'])
            ->published()
            ->orderBy('published_at', 'desc');

        // Filter by category
        $selectedCategory = null;
        if ($request->filled('category')) {
            $selectedCategory = PostCategory::where('slug', $request->category)->first();
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15);

        // Add first image and first 4 images to each post
        $posts->getCollection()->transform(function ($post) {
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

        $categories = PostCategory::active()->orderBy('sort_order')->get();

        return Inertia::render('Community/Posts', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'filters' => $request->only(['category', 'type', 'search']),
        ]);
    }

    public function creators(Request $request)
    {
        $query = CreatorProfile::with('user')
            ->verified()
            ->orderBy('is_featured', 'desc')
            ->orderBy('follower_count', 'desc');

        // Filter by specialty
        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        $creators = $query->paginate(12);

        // Get unique specialties for filter
        $specialties = CreatorProfile::verified()
            ->distinct()
            ->pluck('specialty')
            ->filter()
            ->sort();

        return Inertia::render('Community/Creators', [
            'creators' => $creators,
            'specialties' => $specialties,
            'filters' => $request->only(['specialty', 'search']),
        ]);
    }

    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'all_time');

        $leaderboard = UserPoints::with(['user.creatorProfile'])
            ->orderBy('points', 'desc')
            ->take(50)
            ->get();

        return Inertia::render('Community/Leaderboard', [
            'leaderboard' => $leaderboard,
            'period' => $period,
        ]);
    }
}