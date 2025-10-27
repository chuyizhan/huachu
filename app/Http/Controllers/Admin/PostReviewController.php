<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PostReviewController extends Controller
{
    /**
     * Display posts pending review
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $posts = Post::with(['user', 'category', 'reviewer'])
            ->reviewStatus($status)
            ->latest('created_at')
            ->paginate(20);

        // Add media info to posts
        $posts->getCollection()->transform(function ($post) {
            $post->first_image = $post->getFirstMediaUrl('images', 'thumb');
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
        $post = Post::with(['user.creatorProfile', 'category', 'reviewer'])
            ->findOrFail($id);

        // Add all media
        $post->image_urls = $post->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl(),
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

        return Inertia::render('Admin/Posts/ReviewDetail', [
            'post' => $post,
        ]);
    }

    /**
     * Approve a post
     */
    public function approve(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $post->review_status = 'approved';
        $post->reviewed_by = Auth::id();
        $post->reviewed_at = now();
        $post->review_notes = $request->input('notes');
        $post->save();

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

        $post = Post::findOrFail($id);

        $post->review_status = 'rejected';
        $post->reviewed_by = Auth::id();
        $post->reviewed_at = now();
        $post->review_notes = $request->input('notes');
        $post->save();

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

        Post::whereIn('id', $request->post_ids)->update([
            'review_status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', '已批准 ' . count($request->post_ids) . ' 个帖子');
    }

    /**
     * Reset review status back to pending
     */
    public function resetReview($id)
    {
        $post = Post::findOrFail($id);

        $post->review_status = 'pending';
        $post->reviewed_by = null;
        $post->reviewed_at = null;
        $post->review_notes = null;
        $post->save();

        return redirect()->back()->with('success', '审核状态已重置');
    }
}
