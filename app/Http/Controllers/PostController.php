<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $posts = $user->posts()
            ->with(['category'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => $user->posts()->count(),
            'published' => $user->posts()->published()->count(),
            'drafts' => $user->posts()->where('status', 'draft')->count(),
            'premium' => $user->posts()->where('is_premium', true)->count(),
        ];

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }

    public function show($slug)
    {
        $post = Post::with(['user.creatorProfile', 'category', 'likedByUsers'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment view count
        $post->increment('view_count');

        // Get related posts
        $relatedPosts = Post::with(['user', 'category'])
            ->published()
            ->where('post_category_id', $post->post_category_id)
            ->where('id', '!=', $post->id)
            ->take(4)
            ->get();

        // Check if current user has liked/favorited/following
        $userInteractions = [];
        if (Auth::check()) {
            $user = Auth::user();
            $isFollowingCreator = false;

            // Check if following the post author (if they have a creator profile)
            if ($post->user->creatorProfile) {
                $isFollowingCreator = Follow::isFollowing($user->id, $post->user->creatorProfile->id);
            }

            $userInteractions = [
                'liked' => $user->likedPosts()->where('post_id', $post->id)->exists(),
                'favorited' => $user->favorites()
                    ->where('favoritable_type', Post::class)
                    ->where('favoritable_id', $post->id)
                    ->exists(),
                'following_creator' => $isFollowingCreator,
            ];
        }

        return Inertia::render('Posts/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'userInteractions' => $userInteractions,
        ]);
    }

    public function create()
    {
        $categories = PostCategory::active()->orderBy('sort_order')->get();

        return Inertia::render('Posts/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'post_category_id' => 'required|exists:post_categories,id',
            'type' => 'required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'videos' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'status' => 'required|in:draft,published',
        ]);

        $post = new Post();
        $post->user_id = Auth::id();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->post_category_id = $request->post_category_id;
        $post->type = $request->type;
        $post->images = $request->images ?? [];
        $post->videos = $request->videos ?? [];
        $post->tags = $request->tags ?? [];
        $post->is_premium = $request->boolean('is_premium');
        $post->status = $request->status;

        if ($post->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post created successfully!');
    }

    public function edit($id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        $categories = PostCategory::active()->orderBy('sort_order')->get();

        return Inertia::render('Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'post_category_id' => 'required|exists:post_categories,id',
            'type' => 'required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'videos' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'status' => 'required|in:draft,published',
        ]);

        $post->title = $request->title;
        $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->post_category_id = $request->post_category_id;
        $post->type = $request->type;
        $post->images = $request->images ?? [];
        $post->videos = $request->videos ?? [];
        $post->tags = $request->tags ?? [];
        $post->is_premium = $request->boolean('is_premium');
        $post->status = $request->status;

        if ($request->status === 'published' && !$post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post updated successfully!');
    }

    public function destroy($id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}