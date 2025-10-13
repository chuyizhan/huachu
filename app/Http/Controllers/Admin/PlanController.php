<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    /**
     * Display a listing of plans with filters.
     */
    public function index(Request $request): Response
    {
        $query = Plan::query()->withCount('subscriptions');

        // Search by name or slug
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', (int) $request->input('is_active'));
        }

        // Filter by level
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $plans = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only([
                'search',
                'is_active',
                'level',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Plans/Create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|lowercase|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'period_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'max_premium_posts' => 'nullable|integer|min:0',
            'can_create_premium_content' => 'boolean',
            'priority_support' => 'boolean',
            'badge_color' => 'required|string|max:7',
            'badge_text_color' => 'required|string|max:7',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert checkbox values properly
        $validated['can_create_premium_content'] = $request->boolean('can_create_premium_content');
        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['is_active'] = $request->boolean('is_active');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', '套餐已创建');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan): Response
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'level' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|lowercase|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'period_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'max_premium_posts' => 'nullable|integer|min:0',
            'can_create_premium_content' => 'boolean',
            'priority_support' => 'boolean',
            'badge_color' => 'required|string|max:7',
            'badge_text_color' => 'required|string|max:7',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert checkbox values properly
        $validated['can_create_premium_content'] = $request->boolean('can_create_premium_content');
        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['is_active'] = $request->boolean('is_active');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', '套餐已更新');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Plan $plan)
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = $plan->subscriptions()->where('status', 'active')->count();

        if ($activeSubscriptions > 0) {
            return redirect()->back()->with('error', "无法删除套餐，有 {$activeSubscriptions} 个活跃订阅");
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', '套餐已删除');
    }
}
