<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PostFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle favorite status for a post
     */
    public function toggle(Request $request, Post $post)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $existingFavorite = Favorite::where('user_id', $user->id)
                ->where('favoritable_type', Post::class)
                ->where('favoritable_id', $post->id)
                ->first();

            if ($existingFavorite) {
                // Unfavorite the post
                $existingFavorite->delete();
                $favorited = false;
                $message = '已取消收藏';
            } else {
                // Favorite the post
                Favorite::create([
                    'user_id' => $user->id,
                    'favoritable_type' => Post::class,
                    'favoritable_id' => $post->id,
                ]);
                $favorited = true;
                $message = '收藏成功';
            }

            DB::commit();

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'favorited' => $favorited,
                ]);
            }

            // Redirect back for regular form submissions
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '操作失败，请重试',
                ], 500);
            }

            return back()->with('error', '操作失败，请重试');
        }
    }

    /**
     * Check if user has favorited a post
     */
    public function status(Post $post)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'favorited' => false,
            ]);
        }

        $favorited = Favorite::where('user_id', $user->id)
            ->where('favoritable_type', Post::class)
            ->where('favoritable_id', $post->id)
            ->exists();

        return response()->json([
            'favorited' => $favorited,
        ]);
    }

    /**
     * Display user's favorite posts
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $favorites = Favorite::with(['favoritable.user.creatorProfile', 'favoritable.category'])
            ->where('user_id', $user->id)
            ->where('favoritable_type', Post::class)
            ->latest()
            ->paginate(12);

        // Transform to get the posts
        $favoritePosts = $favorites->through(function ($favorite) {
            return $favorite->favoritable;
        });

        return Inertia::render('User/Favorites', [
            'favorites' => $favoritePosts,
        ]);
    }
}
