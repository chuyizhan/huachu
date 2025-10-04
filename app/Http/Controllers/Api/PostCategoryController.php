<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PostCategory::active()->orderBy('sort_order');

        if ($request->boolean('with_counts')) {
            $query->withCount('posts');
        }

        $categories = $query->get();

        return response()->json($categories);
    }

    public function show($slug)
    {
        $category = PostCategory::where('slug', $slug)
            ->active()
            ->withCount('posts')
            ->firstOrFail();

        // Get recent posts in this category
        $recentPosts = $category->posts()
            ->published()
            ->with(['user', 'category'])
            ->latest('published_at')
            ->take(5)
            ->get();

        return response()->json([
            'category' => $category,
            'recent_posts' => $recentPosts
        ]);
    }
}