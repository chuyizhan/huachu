<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreatorSubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;

class CreatorSubscriptionPlanController extends Controller
{
    /**
     * Display creator's subscription plans.
     */
    public function index(User $creator)
    {
        $plans = CreatorSubscriptionPlan::where('creator_id', $creator->id)
            ->ordered()
            ->get();

        return response()->json($plans);
    }

    /**
     * Store a new subscription plan.
     */
    public function store(Request $request, User $creator)
    {
        // Check creator plan limit (max 3)
        $existingCount = CreatorSubscriptionPlan::where('creator_id', $creator->id)
            ->where('is_active', true)
            ->count();

        if ($existingCount >= 3) {
            return back()->with('error', '最多只能创建3个订阅计划');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $plan = CreatorSubscriptionPlan::create([
            'creator_id' => $creator->id,
            ...$validated,
        ]);

        return back()->with('success', '订阅计划已创建');
    }

    /**
     * Update a subscription plan.
     */
    public function update(Request $request, CreatorSubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update($validated);

        return back()->with('success', '订阅计划已更新');
    }

    /**
     * Delete a subscription plan.
     */
    public function destroy(CreatorSubscriptionPlan $plan)
    {
        // Soft delete or deactivate instead of hard delete
        $plan->update(['is_active' => false]);

        return back()->with('success', '订阅计划已删除');
    }
}
