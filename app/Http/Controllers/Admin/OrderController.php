<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters
     */
    public function index(Request $request): Response
    {
        $query = Order::query()->with(['user:id,name,login_name,email']);

        // Search by user or order number
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('login_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by payment method
        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
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
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $orders = $query->paginate($perPage)->withQueryString();

        // Get users who have orders (for filter)
        $users = User::whereHas('orders')
            ->select('id', 'name', 'login_name')
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $stats = [
            'total_orders' => Order::count(),
            'total_amount' => (float) (Order::where('status', 'completed')->sum('amount') ?? 0),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'failed_orders' => Order::where('status', 'failed')->count(),
            'recharge_orders' => Order::where('type', 'recharge')->count(),
            'recharge_amount' => (float) (Order::where('type', 'recharge')->where('status', 'completed')->sum('amount') ?? 0),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only([
                'search',
                'type',
                'status',
                'payment_method',
                'user_id',
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
