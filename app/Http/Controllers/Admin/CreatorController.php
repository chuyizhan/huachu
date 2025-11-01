<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CreatorProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorController extends Controller
{
    /**
     * Display a listing of creators.
     */
    public function index(Request $request): Response
    {
        $query = User::query()
            ->where('is_creator', true)
            ->with('creatorProfile');

        // Search by name, email, or display_name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('creatorProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('display_name', 'like', "%{$search}%")
                            ->orWhere('specialty', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by verification status
        if ($verificationStatus = $request->input('verification_status')) {
            $query->whereHas('creatorProfile', function ($q) use ($verificationStatus) {
                $q->where('verification_status', $verificationStatus);
            });
        }

        // Filter by featured status
        if ($request->has('is_featured') && $request->input('is_featured') !== '') {
            $isFeatured = (bool) $request->input('is_featured');
            $query->whereHas('creatorProfile', function ($q) use ($isFeatured) {
                $q->where('is_featured', $isFeatured);
            });
        }

        // Filter by top creator status
        if ($request->has('is_top_creator') && $request->input('is_top_creator') !== '') {
            $query->where('is_top_creator', (bool) $request->input('is_top_creator'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if ($sortBy === 'follower_count') {
            $query->join('creator_profiles', 'users.id', '=', 'creator_profiles.user_id')
                ->select('users.*')
                ->orderBy('creator_profiles.follower_count', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Paginate
        $perPage = $request->input('per_page', 20);
        $creators = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Creators/Index', [
            'creators' => $creators,
            'filters' => $request->only([
                'search',
                'verification_status',
                'is_featured',
                'is_top_creator',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Display the specified creator.
     */
    public function show(string $id)
    {
        $user = User::with('creatorProfile')->findOrFail($id);

        if (!$user->is_creator) {
            abort(404, 'Creator not found');
        }

        return Inertia::render('Admin/Creators/Show', [
            'creator' => $user,
        ]);
    }

    /**
     * Update the specified creator.
     */
    public function update(Request $request, string $id)
    {
        $user = User::with('creatorProfile')->findOrFail($id);

        if (!$user->is_creator) {
            abort(404, 'Creator not found');
        }

        $validated = $request->validate([
            'is_top_creator' => 'sometimes|boolean',
            'profile.display_name' => 'sometimes|string|max:255',
            'profile.bio' => 'nullable|string',
            'profile.specialty' => 'nullable|string|max:255',
            'profile.experience_years' => 'sometimes|integer|min:0',
            'profile.location' => 'nullable|string|max:255',
            'profile.website' => 'nullable|url|max:255',
            'profile.portfolio_url' => 'nullable|url|max:255',
            'profile.verification_status' => 'sometimes|in:pending,verified,rejected',
            'profile.verification_notes' => 'nullable|string',
            'profile.is_featured' => 'sometimes|boolean',
        ]);

        // Update user fields
        if (isset($validated['is_top_creator'])) {
            $user->update(['is_top_creator' => $validated['is_top_creator']]);
        }

        // Update creator profile fields
        if (isset($validated['profile']) && $user->creatorProfile) {
            $profileData = $validated['profile'];

            // Set verified_at timestamp when status changes to verified
            if (isset($profileData['verification_status']) && $profileData['verification_status'] === 'verified') {
                $profileData['verified_at'] = now();
            }

            $user->creatorProfile->update($profileData);
        }

        return redirect()->back()->with('success', 'Creator updated successfully');
    }

    /**
     * Remove the creator status from user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (!$user->is_creator) {
            abort(404, 'Creator not found');
        }

        // Disable creator status (observer will soft-delete profile)
        $user->update(['is_creator' => false]);

        return redirect()->back()->with('success', 'Creator status removed successfully');
    }

    /**
     * Verify a creator.
     */
    public function verify(Request $request, string $id)
    {
        $user = User::with('creatorProfile')->findOrFail($id);

        if (!$user->is_creator || !$user->creatorProfile) {
            abort(404, 'Creator not found');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string',
        ]);

        $user->creatorProfile->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? '',
        ]);

        return redirect()->back()->with('success', 'Creator verified successfully');
    }

    /**
     * Reject creator verification.
     */
    public function reject(Request $request, string $id)
    {
        $user = User::with('creatorProfile')->findOrFail($id);

        if (!$user->is_creator || !$user->creatorProfile) {
            abort(404, 'Creator not found');
        }

        $validated = $request->validate([
            'verification_notes' => 'required|string',
        ]);

        $user->creatorProfile->update([
            'verification_status' => 'rejected',
            'verified_at' => null,
            'verification_notes' => $validated['verification_notes'],
        ]);

        return redirect()->back()->with('success', 'Creator verification rejected');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(string $id)
    {
        $user = User::with('creatorProfile')->findOrFail($id);

        if (!$user->is_creator || !$user->creatorProfile) {
            abort(404, 'Creator not found');
        }

        $user->creatorProfile->update([
            'is_featured' => !$user->creatorProfile->is_featured,
        ]);

        return redirect()->back()->with('success', 'Featured status updated');
    }
}
