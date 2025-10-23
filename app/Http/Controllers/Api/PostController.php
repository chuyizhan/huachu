<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category'])
            ->published()
            ->orderBy('published_at', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
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

        return response()->json($posts);
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
            'attachments' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'status' => 'in:draft,published',
        ]);

        $post = new Post();
        $post->user_id = Auth::id();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->post_category_id = $request->post_category_id;
        $post->type = $request->type;
        $post->images = $request->images;
        $post->videos = $request->videos;
        $post->attachments = $request->attachments;
        $post->tags = $request->tags;
        $post->is_premium = $request->boolean('is_premium');
        $post->status = $request->status ?? 'draft';

        if ($post->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load(['user', 'category'])
        ], 201);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'category', 'likedByUsers'])
            ->findOrFail($id);

        // Increment view count only once per session
        $sessionKey = 'post_viewed_' . $post->id;
        if (!session()->has($sessionKey)) {
            $post->increment('view_count');
            session()->put($sessionKey, true);
        }

        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Check if user owns the post or is admin
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'post_category_id' => 'sometimes|required|exists:post_categories,id',
            'type' => 'sometimes|required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'videos' => 'nullable|array',
            'attachments' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'status' => 'in:draft,published',
        ]);

        if ($request->has('title')) {
            $post->title = $request->title;
            $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        }

        $post->fill($request->only([
            'content', 'excerpt', 'post_category_id', 'type',
            'images', 'videos', 'attachments', 'tags'
        ]));

        if ($request->has('is_premium')) {
            $post->is_premium = $request->boolean('is_premium');
        }

        if ($request->has('status')) {
            $post->status = $request->status;
            if ($request->status === 'published' && !$post->published_at) {
                $post->published_at = now();
            }
        }

        $post->save();

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load(['user', 'category'])
        ]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Check if user owns the post or is admin
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function like(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        $existingLike = $user->likedPosts()->where('post_id', $id)->first();

        if ($existingLike) {
            // Unlike
            $user->likedPosts()->detach($id);
            $post->decrement('like_count');
            $liked = false;
        } else {
            // Like
            $user->likedPosts()->attach($id);
            $post->increment('like_count');
            $liked = true;
        }

        return response()->json([
            'message' => $liked ? 'Post liked' : 'Post unliked',
            'liked' => $liked,
            'like_count' => $post->like_count
        ]);
    }

    public function favorite(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        $existingFavorite = $user->favorites()
            ->where('favoritable_type', Post::class)
            ->where('favoritable_id', $id)
            ->first();

        if ($existingFavorite) {
            $existingFavorite->delete();
            $favorited = false;
        } else {
            $user->favorites()->create([
                'favoritable_type' => Post::class,
                'favoritable_id' => $id,
            ]);
            $favorited = true;
        }

        return response()->json([
            'message' => $favorited ? 'Post added to favorites' : 'Post removed from favorites',
            'favorited' => $favorited
        ]);
    }
}