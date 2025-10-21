<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // Get user stats
        $stats = [
            'posts_count' => $user->posts()->count(),
            'followers_count' => $user->creatorProfile ? $user->creatorProfile->followers()->count() : 0,
            'following_count' => $user->following()->count(),
            'favorites_count' => $user->favorites()->count(),
            'subscriptions_count' => $user->subscriptions()->count(),
        ];

        return Inertia::render('Profile/Index', [
            'user' => $user->load('creatorProfile'),
            'stats' => $stats,
        ]);
    }
}
