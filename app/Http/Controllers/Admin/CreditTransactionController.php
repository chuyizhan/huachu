<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreditTransactionController extends Controller
{
    /**
     * Display a listing of credit transactions with comprehensive filters.
     */
    public function index(Request $request): Response
    {
        $query = CreditTransaction::query()
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

        // Filter by credits range
        if ($creditsMin = $request->input('credits_min')) {
            $query->where('credits', '>=', $creditsMin);
        }
        if ($creditsMax = $request->input('credits_max')) {
            $query->where('credits', '<=', $creditsMax);
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
        $reasons = CreditTransaction::select('reason')
            ->distinct()
            ->orderBy('reason')
            ->pluck('reason');

        // Get statistics
        $stats = [
            'total_transactions' => CreditTransaction::count(),
            'total_earned' => CreditTransaction::where('type', 'earned')->sum('credits'),
            'total_spent' => CreditTransaction::where('type', 'spent')->sum('credits'),
            'total_deducted' => CreditTransaction::where('type', 'deducted')->sum('credits'),
            'total_refunded' => CreditTransaction::where('type', 'refunded')->sum('credits'),
        ];

        return Inertia::render('Admin/CreditTransactions/Index', [
            'transactions' => $transactions,
            'users' => $users,
            'reasons' => $reasons,
            'stats' => $stats,
            'filters' => $request->only([
                'search',
                'user_id',
                'type',
                'reason',
                'credits_min',
                'credits_max',
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
     * Show the form for creating a new credit transaction.
     */
    public function create(): Response
    {
        $users = User::select('id', 'name', 'login_name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/CreditTransactions/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created credit transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:earned,spent,deducted,refunded',
            'credits' => 'required|numeric',
            'reason' => 'required|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        // Ensure credits are positive for earned/refunded, negative for spent/deducted
        if (in_array($validated['type'], ['earned', 'refunded']) && $validated['credits'] < 0) {
            $validated['credits'] = abs($validated['credits']);
        } elseif (in_array($validated['type'], ['spent', 'deducted']) && $validated['credits'] > 0) {
            $validated['credits'] = -abs($validated['credits']);
        }

        $transaction = CreditTransaction::create($validated);

        // Update user credits
        $user = User::find($validated['user_id']);
        $user->increment('credits', $validated['credits']);

        return redirect()->route('admin.credit-transactions.index')->with('success', '金币交易已创建');
    }

    /**
     * Remove the specified credit transaction.
     */
    public function destroy(CreditTransaction $creditTransaction)
    {
        // Reverse the transaction
        $user = $creditTransaction->user;
        $user->decrement('credits', $creditTransaction->credits);

        $creditTransaction->delete();

        return redirect()->route('admin.credit-transactions.index')->with('success', '金币交易已删除并回滚');
    }
}
