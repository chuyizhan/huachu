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
        $featuredPosts = Post::with(['user', 'category'])
            ->published()
            ->featured()
            ->take(6)
            ->get();

        // Get recent posts
        $recentPosts = Post::with(['user', 'category'])
            ->published()
            ->latest('published_at')
            ->take(12)
            ->get();

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
        $query = Post::with(['user.creatorProfile', 'category'])
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
        $categories = PostCategory::active()->orderBy('sort_order')->get();

        // Get navigation categories
        $navCategories = PostCategory::where('is_nav_item', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Community/Posts', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'navCategories' => $navCategories,
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