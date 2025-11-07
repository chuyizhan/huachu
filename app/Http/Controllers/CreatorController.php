<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\Follow;
use App\Models\CreatorSubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show', 'index']);
    }

    public function index(Request $request)
    {
        $query = \App\Models\User::with(['creatorProfile'])
            ->where('is_creator', true)
            ->latest();

        $creators = $query->paginate(12);

        // Transform creators to add stats
        $creators->getCollection()->transform(function ($creator) {
            $creator->posts_count = $creator->posts()->published()->count();
            $creator->likes_received = $creator->posts()->sum('like_count');
            $creator->display_name = $creator->creatorProfile->display_name ?? $creator->name;
            $creator->specialty = $creator->creatorProfile->specialty ?? null;
            $creator->bio = $creator->creatorProfile->bio ?? null;
            $creator->verification_status = $creator->creatorProfile->verification_status ?? 'none';

            return $creator;
        });

        return Inertia::render('Creators/Index', [
            'creators' => $creators,
        ]);
    }

    public function show($id)
    {
        $creator = CreatorProfile::with(['user'])
            ->findOrFail($id);

        // Get creator's posts with pagination
        $posts = $creator->user->posts()
            ->published()
            ->where('review_status', 'approved')
            ->with(['category', 'user.creatorProfile'])
            ->latest('published_at')
            ->paginate(12);

        // Calculate stats
        $postsCount = $creator->user->posts()->published()->where('review_status', 'approved')->count();
        $likesReceived = $creator->user->posts()->sum('like_count');

        // Get followers count
        $followersCount = Follow::getFollowersCount($id);

        // Update creator with calculated stats
        $creator->posts_count = $postsCount;
        $creator->likes_received = $likesReceived;
        $creator->followers_count = $followersCount;
        $creator->joined_at = $creator->created_at;

        // Check if current user is following and can follow
        $isFollowing = false;
        $canFollow = Auth::check() && Auth::id() !== $creator->user_id;

        if (Auth::check()) {
            $isFollowing = Follow::isFollowing(Auth::id(), $id);
        }

        // Get subscription plans
        $subscriptionPlans = CreatorSubscriptionPlan::where('creator_id', $creator->user_id)
            ->where('is_active', true)
            ->ordered()
            ->get();

        // Check if user has active subscription
        $hasActiveSubscription = false;
        $activeSubscription = null;
        if (Auth::check()) {
            $activeSubscription = UserSubscription::where('subscriber_id', Auth::id())
                ->where('creator_id', $creator->user_id)
                ->active()
                ->first();
            $hasActiveSubscription = $activeSubscription !== null;
        }

        // Get user's current credits
        $userCredits = Auth::check() ? (float) Auth::user()->credits : 0;

        return Inertia::render('Creator/Show', [
            'creator' => $creator,
            'posts' => $posts,
            'isFollowing' => $isFollowing,
            'canFollow' => $canFollow,
            'subscriptionPlans' => $subscriptionPlans,
            'hasActiveSubscription' => $hasActiveSubscription,
            'activeSubscription' => $activeSubscription,
            'userCredits' => $userCredits,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $profile = $user->creatorProfile;

        if (!$profile) {
            return redirect()->route('creator.apply');
        }

        // Get posts stats
        $postsStats = [
            'total' => $user->posts()->count(),
            'published' => $user->posts()->published()->count(),
            'drafts' => $user->posts()->where('status', 'draft')->count(),
            'premium' => $user->posts()->where('is_premium', true)->count(),
            'total_likes' => $user->posts()->sum('like_count'),
            'total_views' => $user->posts()->sum('view_count'),
        ];

        return Inertia::render('Creators/Profile', [
            'profile' => $profile,
            'postsStats' => $postsStats,
            'userPoints' => $user->points,
        ]);
    }

    public function apply()
    {
        return Inertia::render('Creators/Apply');
    }

    public function edit()
    {
        $user = Auth::user();
        $profile = $user->creatorProfile;

        if (!$profile) {
            return redirect()->route('creator.apply');
        }

        return Inertia::render('Creators/Edit', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = $user->creatorProfile;

        if (!$profile) {
            return redirect()->route('creator.apply');
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'bio' => 'required|string|max:1000',
            'specialty' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0|max:50',
            'certifications' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'portfolio_url' => 'nullable|url',
        ]);

        $profile->update($request->only([
            'display_name', 'bio', 'specialty', 'experience_years',
            'certifications', 'location', 'website', 'social_links', 'portfolio_url'
        ]));

        return redirect()->route('creator.profile')
            ->with('success', 'Creator profile updated successfully!');
    }

    /**
     * Toggle follow status for a creator
     */
    public function toggleFollow(Request $request, $id)
    {
        $user = Auth::user();
        $creator = CreatorProfile::findOrFail($id);

        // Users cannot follow themselves
        if ($user->id === $creator->user_id) {
            return response()->json([
                'success' => false,
                'message' => '不能关注自己',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $existingFollow = Follow::where('follower_id', $user->id)
                ->where('creator_id', $id)
                ->first();

            if ($existingFollow) {
                // Unfollow the creator
                $existingFollow->delete();

                // Decrement counters
                $user->decrementFollowing();
                $creator->user->decrementFollowers();

                $following = false;
                $message = '已取消关注';
            } else {
                // Follow the creator
                Follow::create([
                    'follower_id' => $user->id,
                    'creator_id' => $id,
                ]);

                // Increment counters
                $user->incrementFollowing();
                $creator->user->incrementFollowers();

                $following = true;
                $message = '关注成功';
            }

            // Get updated followers count
            $followersCount = Follow::getFollowersCount($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'following' => $following,
                'followers_count' => $followersCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '操作失败，请重试',
            ], 500);
        }
    }
}