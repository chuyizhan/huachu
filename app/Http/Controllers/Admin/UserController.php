<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserController extends Controller
{
    /**
     * Display a listing of users with search and filters.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search by name, email, or login_name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_name', 'like', "%{$search}%");
            });
        }

        // Filter by admin status
        if ($request->has('is_admin') && $request->input('is_admin') !== '') {
            $query->where('is_admin', (int) $request->input('is_admin'));
        }

        // Filter by creator status
        if ($request->has('is_creator') && $request->input('is_creator') !== '') {
            $query->where('is_creator', (int) $request->input('is_creator'));
        }

        // Filter by verified status
        if ($request->has('is_verified') && $request->input('is_verified') !== '') {
            $query->where('is_verified', (int) $request->input('is_verified'));
        }

        // Filter by user type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by user status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only([
                'search',
                'is_admin',
                'is_creator',
                'is_verified',
                'type',
                'status',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->loadCount(['posts', 'favorites'])
            ->load('creatorProfile');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Debug: Log all incoming request data
        \Log::info('Update user request data:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'login_name' => 'required|string|lowercase|max:32|unique:users,login_name,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_creator' => 'boolean',
            'is_verified' => 'boolean',
            'is_top_creator' => 'boolean',
            'type' => 'required|integer|in:1,2',
            'status' => 'required|integer|in:1,2,3',
            'credits' => 'required|numeric|min:0',
            'balance' => 'required|numeric|min:0',
        ]);

        // Debug: Log validated data
        \Log::info('Validated data:', $validated);

        // Convert checkbox values properly
        $validated['is_creator'] = $request->boolean('is_creator');
        $validated['is_verified'] = $request->boolean('is_verified');
        $validated['is_top_creator'] = $request->boolean('is_top_creator');

        // Always remove password_confirmation from validated data (not a model field)
        unset($validated['password_confirmation']);

        // Handle password update separately
        $updatePassword = false;
        $newPassword = null;

        // Check if password exists and is not empty or null
        $passwordProvided = isset($validated['password']) &&
                           $validated['password'] !== null &&
                           $validated['password'] !== '';

        if ($passwordProvided) {
            $updatePassword = true;
            $newPassword = $validated['password'];
            \Log::info('Password will be updated. New password: ' . $newPassword . ', Length: ' . strlen($newPassword));
        } else {
            \Log::info('No password update. Password value: ' . var_export($validated['password'] ?? 'KEY_NOT_SET', true));
        }

        // Always remove password from validated data
        unset($validated['password']);

        // Update user data
        $user->update($validated);
        \Log::info('User data updated');

        // Update password if provided
        if ($updatePassword) {
            $hashedPassword = Hash::make($newPassword);
            \Log::info('Updating password. Hashed password: ' . substr($hashedPassword, 0, 20) . '...');

            $user->forceFill([
                'password' => $hashedPassword,
                'remember_token' => Str::random(60),
            ])->save();

            \Log::info('Password updated. New password hash from DB: ' . substr($user->fresh()->password, 0, 20) . '...');
        }

        return redirect()->route('admin.users.index')->with('success', '用户已更新');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->back()->with('error', '无法删除管理员账户');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', '用户已删除');
    }

    /**
     * Consolidate user balances based on transaction records.
     */
    public function consolidateBalances(User $user)
    {
        // Calculate total points from point transactions
        $totalPoints = $user->pointTransactions()->sum('points');

        // Calculate total credits from credit transactions
        $totalCredits = $user->creditTransactions()->sum('credits');

        // Update user balances
        $user->update([
            'balance' => $totalPoints,
            'credits' => $totalCredits,
        ]);

        return redirect()->back()->with('success', "余额已重新计算：积分 {$totalPoints}，金币 {$totalCredits}");
    }
}
