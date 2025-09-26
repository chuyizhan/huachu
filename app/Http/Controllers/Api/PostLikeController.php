<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle like status for a post
     */
    public function toggle(Request $request, Post $post)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $existingLike = PostLike::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();

            if ($existingLike) {
                // Unlike the post
                $existingLike->delete();
                $post->decrement('like_count');
                $liked = false;
                $message = 'Post unliked successfully';
            } else {
                // Like the post
                PostLike::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $post->increment('like_count');
                $liked = true;
                $message = 'Post liked successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'liked' => $liked,
                'like_count' => $post->fresh()->like_count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
            ], 500);
        }
    }

    /**
     * Check if user has liked a post
     */
    public function status(Post $post)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'liked' => false,
                'like_count' => $post->like_count,
            ]);
        }

        $liked = PostLike::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->exists();

        return response()->json([
            'liked' => $liked,
            'like_count' => $post->like_count,
        ]);
    }
}
