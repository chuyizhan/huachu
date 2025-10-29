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
                'thumb' => $firstMedia->hasGeneratedConversion('thumb')
                    ? $firstMedia->getUrl('thumb')
                    : $firstMedia->getUrl(),
            ] : null;
            return $post;
        });

        $stats = [
            'total' => $user->posts()->count(),
            'published' => $user->posts()->published()->count(),
            'drafts' => $user->posts()->where('status', 'draft')->count(),
            'premium' => $user->posts()->where('is_premium', true)->count(),
            'pending_review' => $user->posts()->pendingReview()->count(),
            'approved' => $user->posts()->approved()->count(),
            'rejected' => $user->posts()->rejected()->count(),
        ];

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }

    public function show($slug)
    {
        $user = Auth::user();

        $post = Post::with(['user.creatorProfile', 'category', 'likedByUsers', 'media', 'comments'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Check review status - only approved posts are visible publicly
        // Exception: Post author and admins can always view
        if ($post->review_status !== 'approved') {
            // Not approved - check if user has permission to view
            $canViewUnderReview = false;

            if ($user) {
                // Author can view their own post
                if ($user->id === $post->user_id) {
                    $canViewUnderReview = true;
                }
                // Admin can view all posts
                if ($user->is_admin) {
                    $canViewUnderReview = true;
                }
            }

            if (!$canViewUnderReview) {
                abort(404);
            }
        }

        // Check if user can view the content
        $canView = $post->canBeViewedBy($user);
        $isLocked = $post->isLocked();
        $isPurchased = $user ? $post->isPurchasedBy($user) : false;

        // Always show all images regardless of access
        $post->image_urls = $post->getMedia('images')->map(function ($media) {
            // Generate signed URL for Wasabi/S3 (valid for 24 hours), use regular URL for local storage
            $isCloudStorage = $media->disk === 'wasabi' || $media->disk === 's3';

            $url = $isCloudStorage
                ? \Storage::disk($media->disk)->temporaryUrl($media->getPathRelativeToRoot(), now()->addHours(24))
                : $media->getUrl();

            $thumbUrl = $media->hasGeneratedConversion('thumb')
                ? ($isCloudStorage
                    ? \Storage::disk($media->disk)->temporaryUrl($media->getPath('thumb'), now()->addHours(24))
                    : $media->getUrl('thumb'))
                : $url;

            $mediumUrl = $media->hasGeneratedConversion('medium')
                ? ($isCloudStorage
                    ? \Storage::disk($media->disk)->temporaryUrl($media->getPath('medium'), now()->addHours(24))
                    : $media->getUrl('medium'))
                : $url;

            return [
                'id' => $media->id,
                'url' => $url,
                'thumb' => $thumbUrl,
                'medium' => $mediumUrl,
            ];
        });

        // Only show full content and videos if user can view
        if ($canView) {
            // User has access - show videos
            $post->video_urls = $post->getMedia('videos')->map(function ($media) {
                // Generate signed URL for Wasabi/S3 (valid for 24 hours)
                $url = $media->disk === 'wasabi' || $media->disk === 's3'
                    ? \Storage::disk($media->disk)->temporaryUrl($media->getPathRelativeToRoot(), now()->addHours(24))
                    : $media->getUrl();

                return [
                    'id' => $media->id,
                    'url' => $url,
                    'name' => $media->file_name,
                    'size' => $media->size,
                ];
            });
        } else {
            // User doesn't have access - hide text content and videos
            $post->content = null;
            $post->excerpt = null;
            $post->video_urls = [];
            $post->videos = [];
        }

        // Increment view count only once per session
        $sessionKey = 'post_viewed_' . $post->id;
        if (!session()->has($sessionKey)) {
            $post->increment('view_count');
            session()->put($sessionKey, true);
        }

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

        // Check if using Wasabi/S3 for images
        $imagesDisk = config('media.collections.images.disk', config('media.disk', 'public'));
        $useCloudForImages = in_array($imagesDisk, ['s3', 'wasabi']);

        return Inertia::render('Posts/Create', [
            'categories' => $categories,
            'useCloudForImages' => $useCloudForImages,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'post_category_id' => 'required|exists:post_categories,id',
            'type' => 'required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'image_temp_upload_ids' => 'nullable|array',
            'image_temp_upload_ids.*' => 'nullable|exists:temp_media_uploads,id',
            'video_temp_upload_id' => 'nullable|exists:temp_media_uploads,id',
            'videos' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'free_after' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ]);

        // Validate that either content or media exists
        if (empty($request->content) &&
            empty($request->image_temp_upload_ids) &&
            !$request->hasFile('images') &&
            empty($request->video_temp_upload_id)) {
            return back()->withErrors([
                'content' => '帖子至少要有文字内容，图片或者视频任意一种。'
            ])->withInput();
        }

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
            // Auto-approve if admin, otherwise set to pending for review
            if ($user->is_admin) {
                $post->review_status = 'approved';
                $post->reviewed_by = $user->id;
                $post->reviewed_at = now();
                $post->review_notes = 'Auto-approved (Admin post)';
            } else {
                $post->review_status = 'pending';
            }
        } else {
            // Drafts don't need review
            $post->review_status = 'approved';
        }

        $post->save();

        // Handle image uploads - either from temp uploads (Wasabi/S3) or direct file upload (local)
        if ($request->filled('image_temp_upload_ids') && is_array($request->image_temp_upload_ids)) {
            // Handle cloud-uploaded images (from temporary uploads)
            $tempUploadIds = array_slice($request->image_temp_upload_ids, 0, 12); // Limit to 12 images

            foreach ($tempUploadIds as $tempUploadId) {
                $tempUpload = \App\Models\TempMediaUpload::where('id', $tempUploadId)
                    ->where('status', 'confirmed')
                    ->first();

                if ($tempUpload && (!$tempUpload->user_id || $tempUpload->user_id === $user->id)) {
                    if ($tempUpload->s3_path) {
                        $disk = $tempUpload->disk;
                        $tempPath = $tempUpload->s3_path;

                        // Create media record
                        $media = new \Spatie\MediaLibrary\MediaCollections\Models\Media();
                        $media->model_type = Post::class;
                        $media->model_id = $post->id;
                        $media->collection_name = 'images';
                        $media->name = pathinfo($tempUpload->file_name, PATHINFO_FILENAME);
                        $media->file_name = $tempUpload->file_name;
                        $media->disk = $disk;
                        $media->conversions_disk = $disk;
                        $media->size = $tempUpload->file_size;
                        $media->mime_type = $tempUpload->file_type;
                        $media->manipulations = [];
                        $media->custom_properties = [];
                        $media->generated_conversions = [];
                        $media->responsive_images = [];
                        $media->uuid = \Illuminate\Support\Str::uuid();
                        $media->save();

                        // Copy file to permanent location using CustomPathGenerator
                        // Use getPathRelativeToRoot() which returns the path relative to storage root
                        $permanentPath = $media->getPathRelativeToRoot();
                        \Storage::disk($disk)->copy($tempPath, $permanentPath);

                        // Delete temp file and record
                        \Storage::disk($disk)->delete($tempPath);
                        $tempUpload->delete();
                    }
                }
            }
        } elseif ($request->hasFile('images')) {
            // Handle direct file upload (for local storage)
            $files = $request->file('images');
            // Limit to 12 images
            $files = array_slice($files, 0, 12);

            foreach ($files as $file) {
                $post->addMedia($file)
                    ->withResponsiveImages()
                    ->toMediaCollection('images');
            }
        }

        // Handle S3 uploaded video (from temporary upload)
        if ($request->video_temp_upload_id) {
            $tempUpload = \App\Models\TempMediaUpload::where('id', $request->video_temp_upload_id)
                ->where('status', 'confirmed')
                ->first();

            // Verify user owns this upload (if user_id is set)
            if ($tempUpload && (!$tempUpload->user_id || $tempUpload->user_id === $user->id)) {
                if ($tempUpload->s3_path) {
                    $disk = $tempUpload->disk;
                    $tempPath = $tempUpload->s3_path;

                    // Create media record first to get the ID
                    $media = new \Spatie\MediaLibrary\MediaCollections\Models\Media();
                    $media->model_type = Post::class;
                    $media->model_id = $post->id;
                    $media->collection_name = 'videos';
                    $media->name = pathinfo($tempUpload->file_name, PATHINFO_FILENAME);
                    $media->file_name = $tempUpload->file_name;
                    $media->disk = $disk;
                    $media->conversions_disk = $disk;
                    $media->size = $tempUpload->file_size;
                    $media->mime_type = $tempUpload->file_type;
                    $media->manipulations = [];
                    $media->custom_properties = [];
                    $media->generated_conversions = [];
                    $media->responsive_images = [];
                    $media->uuid = \Illuminate\Support\Str::uuid();
                    $media->save();

                    // CustomPathGenerator will automatically use: posts/{postId}/videos/{mediaId}/
                    // Use getPathRelativeToRoot() which returns the path relative to storage root
                    $permanentPath = $media->getPathRelativeToRoot();
                    \Storage::disk($disk)->copy($tempPath, $permanentPath);

                    // Delete temp file and record
                    \Storage::disk($disk)->delete($tempPath);
                    $tempUpload->delete();
                }
            }
        }

        // Award points if post is published and has minimum required images
        if ($post->status === 'published' && config('points.post_creation.enabled')) {
            $imageCount = $post->getMedia('images')->count();

            // Get category-specific settings or fall back to defaults
            $category = $post->category;
            $minImages = $category->minimum_images > 0
                ? $category->minimum_images
                : config('points.post_creation.default_minimum_images');
            $pointsToAward = $category->points_reward > 0
                ? $category->points_reward
                : config('points.post_creation.default_points');

            // Only award if category has points enabled and meets minimum images
            if ($pointsToAward > 0 && $imageCount >= $minImages) {
                $user->awardPoints(
                    $pointsToAward,
                    'post_created_with_images',
                    $post,
                    [
                        'image_count' => $imageCount,
                        'minimum_required' => $minImages,
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                    ]
                );
            }
        }

        // Send Telegram notification if post is published
        if ($post->status === 'published') {
            try {
                $telegramNotifiable = new \App\Services\TelegramNotifiable();
                $telegramNotifiable->notify(new \App\Notifications\NewPostCreated($post));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Illuminate\Support\Facades\Log::error('Failed to send Telegram notification', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
            }
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
                'thumb' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl(),
                'medium' => $media->hasGeneratedConversion('medium')
                    ? $media->getUrl('medium')
                    : $media->getUrl(),
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
            'content' => 'nullable|string',
            'post_category_id' => 'required|exists:post_categories,id',
            'type' => 'required|in:discussion,tutorial,showcase,question',
            'excerpt' => 'nullable|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'remove_images' => 'nullable|array',
            'video_temp_upload_id' => 'nullable|exists:temporary_uploads,id', // S3 uploaded video
            'video_media_id' => 'nullable|exists:media,id', // Legacy: Pre-uploaded video
            'remove_video' => 'nullable|boolean',
            'videos' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_premium' => 'boolean',
            'status' => 'required|in:draft,published',
        ]);

        // Calculate if post will have any media after this update
        $hasExistingImages = $post->getMedia('images')->count() > 0;
        $hasExistingVideos = $post->getMedia('videos')->count() > 0;
        $willRemoveImages = $request->filled('remove_images') && count($request->remove_images) > 0;
        $willRemoveVideos = $request->boolean('remove_video');
        $hasNewImages = $request->hasFile('images') || $request->filled('image_temp_upload_ids');
        $hasNewVideo = $request->filled('video_temp_upload_id') || $request->filled('video_media_id');

        // Check if there will be any images left
        $willHaveImages = ($hasExistingImages && !$willRemoveImages) || $hasNewImages;
        // Check if there will be any videos left
        $willHaveVideos = ($hasExistingVideos && !$willRemoveVideos) || $hasNewVideo;

        // Validate that either content or media exists
        if (empty($request->content) && !$willHaveImages && !$willHaveVideos) {
            return back()->withErrors([
                'content' => '帖子至少要有文字内容，图片或者视频任意一种。'
            ])->withInput();
        }

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

        // Handle status change from draft to published
        if ($request->status === 'published' && !$post->published_at) {
            $post->published_at = now();
            // Auto-approve if admin, otherwise reset to pending for review
            $user = Auth::user();
            if ($user->is_admin) {
                $post->review_status = 'approved';
                $post->reviewed_by = $user->id;
                $post->reviewed_at = now();
                $post->review_notes = 'Auto-approved (Admin post)';
            } else {
                $post->review_status = 'pending';
                $post->reviewed_by = null;
                $post->reviewed_at = null;
                $post->review_notes = null;
            }
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
            $remainingSlots = 12 - $currentCount;
            $files = array_slice($request->file('images'), 0, $remainingSlots);

            foreach ($files as $file) {
                $post->addMedia($file)
                    ->withResponsiveImages()
                    ->toMediaCollection('images');
            }
        }

        // Handle video removal
        if ($request->boolean('remove_video')) {
            $post->clearMediaCollection('videos');
            $post->videos = [];
            $post->save();
        }

        // Handle S3 uploaded video (from temporary upload)
        if ($request->video_temp_upload_id) {
            $tempUpload = \App\Models\TemporaryUpload::where('id', $request->video_temp_upload_id)
                ->where('user_id', Auth::id())
                ->where('confirmed', true)
                ->first();

            if ($tempUpload && $tempUpload->s3_path) {
                // Clear existing videos first
                $post->clearMediaCollection('videos');

                // Store S3 video URL in videos array
                $videoUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url($tempUpload->s3_path);
                $post->videos = [$videoUrl];
                $post->save();

                // Delete the temporary upload record
                $tempUpload->delete();
            }
        }
        // Legacy: Handle pre-uploaded video (attach to post via media library)
        elseif ($request->video_media_id) {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($request->video_media_id);

            if ($media) {
                // Verify the media belongs to a temporary upload by this user
                $tempUpload = \App\Models\TemporaryUpload::where('user_id', Auth::id())
                    ->whereHas('media', function ($query) use ($request) {
                        $query->where('id', $request->video_media_id);
                    })
                    ->first();

                if ($tempUpload) {
                    // Clear existing videos first (only one video allowed)
                    $post->clearMediaCollection('videos');

                    // Transfer media from temporary upload to post
                    $media->model_type = Post::class;
                    $media->model_id = $post->id;
                    $media->collection_name = 'videos';
                    $media->save();

                    // Delete the temporary upload record (media is now attached to post)
                    $tempUpload->delete();
                }
            }
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