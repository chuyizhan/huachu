<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostPurchase;
use App\Models\PlatformTransaction;
use App\Models\CreditTransaction;
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
                'message' => '金币不足',
                'required' => $post->price,
                'current' => $user->credits,
            ], 400);
        }

        try {
            DB::beginTransaction();

            $totalPrice = $post->price;
            $commissionPercentage = config('platform.commission_percentage', 30);

            // Calculate platform commission and creator earnings
            $platformCommission = ($totalPrice * $commissionPercentage) / 100;
            $creatorEarnings = $totalPrice - $platformCommission;

            // Deduct credits from user
            $user->credits -= $totalPrice;
            $user->save();

            // Record user's credit transaction (spent)
            CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'spent',
                'credits' => -$totalPrice,
                'reason' => 'post_purchase',
                'metadata' => [
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                ],
                'related_type' => Post::class,
                'related_id' => $post->id,
            ]);

            // Credit the author (only their portion after commission)
            $author = $post->user;
            $author->credits += $creatorEarnings;
            $author->save();

            // Record author's credit transaction (earned)
            CreditTransaction::create([
                'user_id' => $author->id,
                'type' => 'earned',
                'credits' => $creatorEarnings,
                'reason' => 'post_sold',
                'metadata' => [
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                    'buyer_id' => $user->id,
                    'total_price' => $totalPrice,
                    'platform_commission' => $platformCommission,
                ],
                'related_type' => Post::class,
                'related_id' => $post->id,
            ]);

            // Create purchase record
            $purchase = PostPurchase::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'price_paid' => $totalPrice,
            ]);

            // Record platform commission transaction
            PlatformTransaction::create([
                'post_purchase_id' => $purchase->id,
                'post_id' => $post->id,
                'creator_id' => $author->id,
                'amount' => $platformCommission,
                'percentage' => $commissionPercentage,
                'creator_amount' => $creatorEarnings,
                'total_transaction' => $totalPrice,
                'metadata' => [
                    'buyer_id' => $user->id,
                    'post_title' => $post->title,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '购买成功',
                'remaining_credits' => $user->credits,
                'transaction_details' => [
                    'total_paid' => $totalPrice,
                    'creator_received' => $creatorEarnings,
                    'platform_fee' => $platformCommission,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '购买失败，请重试',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
