<?php

namespace App\Observers;

use App\Models\User;
use App\Models\CreatorProfile;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Auto-create creator profile if is_creator flag is set
        if ($user->is_creator && !$user->creatorProfile) {
            $this->createCreatorProfile($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Auto-create creator profile if is_creator flag was just enabled
        if ($user->is_creator && !$user->creatorProfile) {
            $this->createCreatorProfile($user);
        }

        // If is_creator was disabled, soft delete the creator profile
        if (!$user->is_creator && $user->creatorProfile && !$user->creatorProfile->trashed()) {
            $user->creatorProfile->delete();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    /**
     * Create a default creator profile for the user.
     */
    private function createCreatorProfile(User $user): void
    {
        CreatorProfile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'bio' => '',
            'specialty' => '创作者', // Default specialty
            'experience_years' => 0,
            'certifications' => [],
            'location' => '',
            'website' => '',
            'social_links' => [],
            'portfolio_url' => '',
            'verification_status' => 'pending',
            'verified_at' => null,
            'verification_notes' => '',
            'is_featured' => false,
            'follower_count' => 0,
            'following_count' => 0,
            'rating' => 0,
            'review_count' => 0,
        ]);
    }
}
