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
            ->with(['category', 'media'])
            ->latest()
            ->paginate(10);

        // Add first image to each post
        $posts->getCollection()->transform(function ($post) {
            $firstMedia = $post->getFirstMedia('images');
            $post->first_image = $firstMedia ? [
                'url' => $firstMedia->getUrl(),
                'thumb' => $firstMedia->getUrl('thumb'),
            ] : null;
            return $post;
        });

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
        $post = Post::with(['user.creatorProfile', 'category', 'likedByUsers', 'media', 'comments'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $user = Auth::user();

        // Check if user can view the content
        $canView = $post->canBeViewedBy($user);
        $isLocked = $post->isLocked();
        $isPurchased = $user ? $post->isPurchasedBy($user) : false;

        // Only show full content and media if user can view
        if ($canView) {
            // User has access - show full content and all media
            $post->image_urls = $post->getMedia('images')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                    'medium' => $media->getUrl('medium'),
                ];
            });

            $post->video_urls = $post->getMedia('videos')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->file_name,
                    'size' => $media->size,
                ];
            });
        } else {
            // User doesn't have access - completely hide content and media
            $post->content = null;
            $post->excerpt = null;
            $post->image_urls = [];
            $post->video_urls = [];
            $post->videos = [];
        }

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
        if ($user) {
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

        // Get comments count
        $commentsCount = $post->comments()->count();

        return Inertia::render('Posts/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'userInteractions' => $userInteractions,
            'canViewContent' => $canView,
            'isLocked' => $isLocked,
            'isPurchased' => $isPurchased,
            'userCredits' => $user ? (float) $user->credits : 0,
            'commentsCount' => $commentsCount,
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
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'post_category_id' => 'required|exists:post_categories,id',
            'type' => 'required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,mov,avi|max:102400', // 100MB max
            'videos' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'free_after' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ]);

        $post = new Post();
        $post->user_id = $user->id;
        $post->title = $request->title;
        $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->post_category_id = $request->post_category_id;
        $post->type = $request->type;
        $post->videos = $request->videos ?? [];
        $post->tags = $request->tags ?? [];
        $post->is_premium = $request->boolean('is_premium');
        $post->status = $request->status;

        // Only allow creators to set price
        if ($user->is_creator) {
            $post->price = $request->price > 0 ? $request->price : null;
            $post->free_after = $request->free_after;
        }

        if ($post->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        // Handle image uploads with Media Library
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Limit to 4 images
            $files = array_slice($files, 0, 4);

            foreach ($files as $file) {
                $post->addMedia($file)
                    ->toMediaCollection('images');
            }
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $post->addMedia($request->file('video'))
                ->toMediaCollection('videos');
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post created successfully!');
    }

    public function edit($id)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        $categories = PostCategory::active()->orderBy('sort_order')->get();

        // Load existing media
        $post->existing_images = $post->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
            ];
        });

        $post->existing_videos = $post->getMedia('videos')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->file_name,
                'size' => $media->size,
            ];
        });

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
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'remove_images' => 'nullable|array',
            'video' => 'nullable|file|mimes:mp4,webm,mov,avi|max:102400', // 100MB max
            'remove_video' => 'nullable|boolean',
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
        $post->videos = $request->videos ?? [];
        $post->tags = $request->tags ?? [];
        $post->is_premium = $request->boolean('is_premium');
        $post->status = $request->status;

        if ($request->status === 'published' && !$post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        // Handle image removals
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $mediaId) {
                $post->deleteMedia($mediaId);
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $currentCount = $post->getMedia('images')->count();
            $remainingSlots = 4 - $currentCount;
            $files = array_slice($request->file('images'), 0, $remainingSlots);

            foreach ($files as $file) {
                $post->addMedia($file)
                    ->toMediaCollection('images');
            }
        }

        // Handle video removal
        if ($request->boolean('remove_video')) {
            $post->clearMediaCollection('videos');
        }

        // Handle new video upload
        if ($request->hasFile('video')) {
            // Clear existing videos first (only one video allowed)
            $post->clearMediaCollection('videos');

            $post->addMedia($request->file('video'))
                ->toMediaCollection('videos');
        }

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