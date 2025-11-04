<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FollowController extends Controller
{
    /**
     * Display the user's following list.
     */
    public function myFollowing()
    {
        $user = Auth::user();

        // Get all creators the user is following
        $following = Follow::with([
            'creator.user'
        ])
            ->where('follower_id', $user->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('Follow/MyFollowing', [
            'following' => $following,
        ]);
    }
}
