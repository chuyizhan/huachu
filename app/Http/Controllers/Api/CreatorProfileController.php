<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreatorProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = CreatorProfile::with(['user'])
            ->verified()
            ->orderBy('is_featured', 'desc')
            ->orderBy('follower_count', 'desc');

        // Filter by specialty
        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $creators = $query->paginate(12);

        return response()->json($creators);
    }

    public function store(Request $request)
    {
        // Check if user already has a creator profile
        if (Auth::user()->creatorProfile) {
            return response()->json(['message' => 'Creator profile already exists'], 400);
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

        $profile = CreatorProfile::create([
            'user_id' => Auth::id(),
            'display_name' => $request->display_name,
            'bio' => $request->bio,
            'specialty' => $request->specialty,
            'experience_years' => $request->experience_years,
            'certifications' => $request->certifications ?? [],
            'location' => $request->location,
            'website' => $request->website,
            'social_links' => $request->social_links ?? [],
            'portfolio_url' => $request->portfolio_url,
            'verification_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Creator profile created successfully. Verification pending.',
            'profile' => $profile->load('user')
        ], 201);
    }

    public function show($id)
    {
        $profile = CreatorProfile::with(['user', 'subscribers'])
            ->findOrFail($id);

        // Get creator's recent posts
        $recentPosts = $profile->user->posts()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get();

        return response()->json([
            'profile' => $profile,
            'recent_posts' => $recentPosts,
            'subscriber_count' => $profile->subscribers()->where('status', 'active')->count()
        ]);
    }

    public function update(Request $request, $id)
    {
        $profile = CreatorProfile::findOrFail($id);

        // Check if user owns the profile
        if ($profile->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'display_name' => 'sometimes|required|string|max:255',
            'bio' => 'sometimes|required|string|max:1000',
            'specialty' => 'sometimes|required|string|max:255',
            'experience_years' => 'sometimes|required|integer|min:0|max:50',
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

        return response()->json([
            'message' => 'Creator profile updated successfully',
            'profile' => $profile->load('user')
        ]);
    }

    public function destroy($id)
    {
        $profile = CreatorProfile::findOrFail($id);

        // Check if user owns the profile
        if ($profile->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile->delete();

        return response()->json(['message' => 'Creator profile deleted successfully']);
    }

    public function follow(Request $request, $id)
    {
        $profile = CreatorProfile::findOrFail($id);
        $user = Auth::user();

        if ($profile->user_id === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        // Check if already following using the Follow model
        $existingFollow = Follow::where('follower_id', $user->id)
            ->where('creator_id', $id)
            ->first();

        if ($existingFollow) {
            $existingFollow->delete();
            $profile->decrement('follower_count');

            // Decrement user counters
            $user->decrementFollowing();
            $profile->user->decrementFollowers();

            $following = false;
        } else {
            Follow::create([
                'follower_id' => $user->id,
                'creator_id' => $id,
            ]);
            $profile->increment('follower_count');

            // Increment user counters
            $user->incrementFollowing();
            $profile->user->incrementFollowers();

            $following = true;
        }

        return response()->json([
            'message' => $following ? 'Following creator' : 'Unfollowed creator',
            'following' => $following,
            'follower_count' => $profile->follower_count
        ]);
    }

    public function myProfile()
    {
        $profile = Auth::user()->creatorProfile;

        if (!$profile) {
            return response()->json(['message' => 'Creator profile not found'], 404);
        }

        // Get creator's posts stats
        $user = Auth::user();
        $postsStats = [
            'total' => $user->posts()->count(),
            'published' => $user->posts()->published()->count(),
            'drafts' => $user->posts()->where('status', 'draft')->count(),
            'premium' => $user->posts()->where('is_premium', true)->count(),
        ];

        return response()->json([
            'profile' => $profile,
            'posts_stats' => $postsStats,
            'points' => $user->points
        ]);
    }
}