<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostPurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Purchase a post with credits
     */
    public function purchase(Request $request, $postId)
    {
        $user = Auth::user();
        $post = Post::findOrFail($postId);

        // Check if post is locked
        if (!$post->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => '此内容是免费的',
            ], 400);
        }

        // Check if already purchased
        if ($post->isPurchasedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => '你已经购买过此内容',
            ], 400);
        }

        // Check if user is the author
        if ($post->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => '不能购买自己的内容',
            ], 400);
        }

        // Check if user has enough credits
        if ($user->credits < $post->price) {
            return response()->json([
                'success' => false,
                'message' => '积分不足',
                'required' => $post->price,
                'current' => $user->credits,
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Deduct credits from user
            $user->credits -= $post->price;
            $user->save();

            // Credit the author
            $author = $post->user;
            $author->credits += $post->price;
            $author->save();

            // Create purchase record
            PostPurchase::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'price_paid' => $post->price,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '购买成功',
                'remaining_credits' => $user->credits,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '购买失败，请重试',
            ], 500);
        }
    }
}
