<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformTransaction;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformTransactionController extends Controller
{
    /**
     * Display platform earnings with filters and statistics.
     */
    public function index(Request $request): Response
    {
        $query = PlatformTransaction::query()
            ->with(['creator:id,name,login_name', 'post:id,title,slug']);

        // Search by creator or post
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('creator', function ($creatorQuery) use ($search) {
                    $creatorQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('login_name', 'like', "%{$search}%");
                })
                ->orWhereHas('post', function ($postQuery) use ($search) {
                    $postQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Filter by creator
        if ($creatorId = $request->input('creator_id')) {
            $query->where('creator_id', $creatorId);
        }

        // Filter by amount range
        if ($amountMin = $request->input('amount_min')) {
            $query->where('amount', '>=', $amountMin);
        }
        if ($amountMax = $request->input('amount_max')) {
            $query->where('amount', '<=', $amountMax);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $transactions = $query->paginate($perPage)->withQueryString();

        // Get creators for filter
        $creators = User::whereHas('platformTransactions')
            ->select('id', 'name', 'login_name')
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $stats = [
            'total_earnings' => (float) (PlatformTransaction::sum('amount') ?? 0),
            'total_transactions' => PlatformTransaction::count(),
            'total_creator_payments' => (float) (PlatformTransaction::sum('creator_amount') ?? 0),
            'average_commission' => (float) (PlatformTransaction::avg('amount') ?? 0),
            'total_revenue' => (float) (PlatformTransaction::sum('total_transaction') ?? 0),
        ];

        // Monthly earnings for the current year
        $monthlyEarnings = PlatformTransaction::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        return Inertia::render('Admin/PlatformTransactions/Index', [
            'transactions' => $transactions,
            'creators' => $creators,
            'stats' => $stats,
            'monthlyEarnings' => $monthlyEarnings,
            'filters' => $request->only([
                'search',
                'creator_id',
                'amount_min',
                'amount_max',
                'date_from',
                'date_to',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }
}
