<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CreatorProfile;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CreatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    public function show($id)
    {
        $creator = CreatorProfile::with(['user'])
            ->verified()
            ->findOrFail($id);

        // Get creator's recent posts
        $recentPosts = $creator->user->posts()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->take(6)
            ->get();

        // Get creator stats
        $stats = [
            'total_posts' => $creator->user->posts()->published()->count(),
            'total_likes' => $creator->user->posts()->sum('like_count'),
            'total_views' => $creator->user->posts()->sum('view_count'),
            'followers' => $creator->follower_count,
        ];

        // Check if current user is following
        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = Auth::user()->favorites()
                ->where('favoritable_type', CreatorProfile::class)
                ->where('favoritable_id', $id)
                ->exists();
        }

        return Inertia::render('Creators/Show', [
            'creator' => $creator,
            'recentPosts' => $recentPosts,
            'stats' => $stats,
            'isFollowing' => $isFollowing,
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
        $user = Auth::user();

        if ($user->creatorProfile) {
            return redirect()->route('creator.profile');
        }

        return Inertia::render('Creators/Apply');
    }

    public function storeApplication(Request $request)
    {
        $user = Auth::user();

        if ($user->creatorProfile) {
            return redirect()->route('creator.profile')
                ->with('error', 'You already have a creator profile.');
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
            'user_id' => $user->id,
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

        return redirect()->route('creator.profile')
            ->with('success', 'Creator profile created successfully! Verification is pending.');
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
}