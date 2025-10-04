<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPointsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $points = $user->points;

        if (!$points) {
            $points = UserPoints::create([
                'user_id' => $user->id,
                'points' => 0,
                'lifetime_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100
            ]);
        }

        // Get recent transactions
        $recentTransactions = PointTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'points' => $points,
            'recent_transactions' => $recentTransactions
        ]);
    }

    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'all_time'); // all_time, monthly, weekly

        $query = UserPoints::with('user')
            ->orderBy('points', 'desc')
            ->take(50);

        if ($period === 'monthly') {
            // This would require more complex logic with monthly point tracking
            // For now, just return all time
        }

        $leaderboard = $query->get();

        return response()->json($leaderboard);
    }
}