<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PointTransactionController extends Controller
{
    /**
     * Display a listing of point transactions with comprehensive filters.
     */
    public function index(Request $request): Response
    {
        $query = PointTransaction::query()
            ->with(['user:id,name,login_name,email']);

        // Search by reason or user info
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('login_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by transaction type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by reason
        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }

        // Filter by points range
        if ($pointsMin = $request->input('points_min')) {
            $query->where('points', '>=', $pointsMin);
        }
        if ($pointsMax = $request->input('points_max')) {
            $query->where('points', '<=', $pointsMax);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo);
        }

        // Filter by related type
        if ($relatedType = $request->input('related_type')) {
            $query->where('related_type', $relatedType);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $transactions = $query->paginate($perPage)->withQueryString();

        // Get users for filter
        $users = User::select('id', 'name', 'login_name')
            ->orderBy('name')
            ->get();

        // Get distinct reasons for filter
        $reasons = PointTransaction::select('reason')
            ->distinct()
            ->orderBy('reason')
            ->pluck('reason');

        // Get statistics
        $stats = [
            'total_transactions' => PointTransaction::count(),
            'total_earned' => PointTransaction::where('type', 'earned')->sum('points'),
            'total_spent' => PointTransaction::where('type', 'spent')->sum('points'),
            'total_deducted' => PointTransaction::where('type', 'deducted')->sum('points'),
        ];

        return Inertia::render('Admin/PointTransactions/Index', [
            'transactions' => $transactions,
            'users' => $users,
            'reasons' => $reasons,
            'stats' => $stats,
            'filters' => $request->only([
                'search',
                'user_id',
                'type',
                'reason',
                'points_min',
                'points_max',
                'date_from',
                'date_to',
                'related_type',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Show the form for creating a new point transaction.
     */
    public function create(): Response
    {
        $users = User::select('id', 'name', 'login_name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PointTransactions/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created point transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:earned,spent,deducted',
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        // Ensure points are positive for earned, negative for spent/deducted
        if ($validated['type'] === 'earned' && $validated['points'] < 0) {
            $validated['points'] = abs($validated['points']);
        } elseif (in_array($validated['type'], ['spent', 'deducted']) && $validated['points'] > 0) {
            $validated['points'] = -abs($validated['points']);
        }

        $transaction = PointTransaction::create($validated);

        // Update user points
        $user = User::find($validated['user_id']);
        $user->increment('points', $validated['points']);

        // Update user_points if exists
        $userPoints = $user->points();
        if ($userPoints->exists()) {
            $userPoints->increment('points', $validated['points']);
            if ($validated['points'] > 0) {
                $userPoints->increment('lifetime_points', $validated['points']);
            }
        }

        return redirect()->route('admin.point-transactions.index')->with('success', '积分交易已创建');
    }

    /**
     * Remove the specified point transaction.
     */
    public function destroy(PointTransaction $pointTransaction)
    {
        // Reverse the transaction
        $user = $pointTransaction->user;
        $user->decrement('points', $pointTransaction->points);

        // Update user_points if exists
        $userPoints = $user->points();
        if ($userPoints->exists()) {
            $userPoints->decrement('points', $pointTransaction->points);
        }

        $pointTransaction->delete();

        return redirect()->route('admin.point-transactions.index')->with('success', '积分交易已删除并回滚');
    }
}
