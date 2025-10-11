<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load(['creatorProfile', 'posts', 'favorites']);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'login_name' => 'sometimes|string|lowercase|max:32|unique:users,login_name,' . $user->id,
            'is_admin' => 'sometimes|boolean',
            'is_creator' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'is_top_creator' => 'sometimes|boolean',
            'type' => 'sometimes|integer',
            'status' => 'sometimes|integer',
            'credits' => 'sometimes|numeric|min:0',
            'balance' => 'sometimes|numeric|min:0',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', '用户已更新');
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
}
