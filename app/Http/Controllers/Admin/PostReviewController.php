<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Notifications\PostReviewStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class PostReviewController extends Controller
{
    /**
     * Display posts pending review
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $posts = Post::with(['user', 'category', 'reviewer', 'media'])
            ->reviewStatus($status)
            ->latest('created_at')
            ->paginate(20);

        // Add media info to posts
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

                $post->first_image = $thumbUrl;
            } else {
                $post->first_image = null;
            }

            $post->images_count = $post->getMedia('images')->count();
            $post->videos_count = $post->getMedia('videos')->count();
            return $post;
        });

        $stats = [
            'pending' => Post::pendingReview()->count(),
            'approved' => Post::approved()->count(),
            'rejected' => Post::rejected()->count(),
        ];

        return Inertia::render('Admin/Posts/Review', [
            'posts' => $posts,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Show a single post for review
     */
    public function show($id)
    {
        $post = Post::with(['user.creatorProfile', 'category', 'reviewer', 'media'])
            ->findOrFail($id);

        // Add all media with cloud storage support
        $post->image_urls = $post->getMedia('images')->map(function ($media) {
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
                'id' => $media->id,
                'url' => $url,
                'thumb' => $thumbUrl,
            ];
        });

        $post->video_urls = $post->getMedia('videos')->map(function ($media) {
            $isCloudStorage = $media->disk === 'wasabi' || $media->disk === 's3';

            $url = $isCloudStorage
                ? \Storage::disk($media->disk)->temporaryUrl($media->getPathRelativeToRoot(), now()->addHours(24))
                : $media->getUrl();

            return [
                'id' => $media->id,
                'url' => $url,
                'name' => $media->file_name,
                'size' => $media->size,
            ];
        });

        return Inertia::render('Admin/Posts/ReviewDetail', [
            'post' => $post,
        ]);
    }

    /**
     * Approve a post
     */
    public function approve(Request $request, $id)
    {
        $post = Post::with(['user', 'category'])->findOrFail($id);

        $post->review_status = 'approved';
        $post->reviewed_by = Auth::id();
        $post->reviewed_at = now();
        $post->review_notes = $request->input('notes');
        $post->save();

        // Send Telegram notification
        $this->sendTelegramNotification($post, 'approved', Auth::user(), $request->input('notes'));

        return redirect()->back()->with('success', '帖子已批准');
    }

    /**
     * Reject a post
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $post = Post::with(['user', 'category'])->findOrFail($id);

        $post->review_status = 'rejected';
        $post->reviewed_by = Auth::id();
        $post->reviewed_at = now();
        $post->review_notes = $request->input('notes');
        $post->save();

        // Send Telegram notification
        $this->sendTelegramNotification($post, 'rejected', Auth::user(), $request->input('notes'));

        return redirect()->back()->with('success', '帖子已拒绝');
    }

    /**
     * Batch approve posts
     */
    public function batchApprove(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
        ]);

        $posts = Post::with(['user', 'category'])->whereIn('id', $request->post_ids)->get();

        Post::whereIn('id', $request->post_ids)->update([
            'review_status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Send Telegram notification for each approved post
        foreach ($posts as $post) {
            $post->review_status = 'approved'; // Update the model instance
            $this->sendTelegramNotification($post, 'approved', Auth::user());
        }

        return redirect()->back()->with('success', '已批准 ' . count($request->post_ids) . ' 个帖子');
    }

    /**
     * Reset review status back to pending
     */
    public function resetReview($id)
    {
        $post = Post::with(['user', 'category'])->findOrFail($id);

        $post->review_status = 'pending';
        $post->reviewed_by = null;
        $post->reviewed_at = null;
        $post->review_notes = null;
        $post->save();

        // Send Telegram notification
        $this->sendTelegramNotification($post, 'reset', Auth::user());

        return redirect()->back()->with('success', '审核状态已重置');
    }

    /**
     * Send Telegram notification for post review status change
     */
    protected function sendTelegramNotification($post, $action, $reviewer, $notes = null)
    {
        // Only send notification if Telegram is configured
        if (!config('services.telegram-bot-api.token') || !config('services.telegram-bot-api.chat_id')) {
            return;
        }

        try {
            Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
                ->notify(new PostReviewStatusChanged($post, $action, $reviewer, $notes));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send Telegram notification: ' . $e->getMessage());
        }
    }
}
