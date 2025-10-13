<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of posts with comprehensive search and filters.
     */
    public function index(Request $request): Response
    {
        $query = Post::query()
            ->with(['user:id,name,login_name', 'category:id,name,slug'])
            ->withCount(['likes', 'purchases']);

        // Search by title, slug, excerpt, or content
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by user/author
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('post_category_id', $categoryId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by featured
        if ($request->has('is_featured') && $request->input('is_featured') !== '') {
            $query->where('is_featured', (int) $request->input('is_featured'));
        }

        // Filter by premium
        if ($request->has('is_premium') && $request->input('is_premium') !== '') {
            $query->where('is_premium', (int) $request->input('is_premium'));
        }

        // Filter by paid/free
        if ($request->input('price_filter') === 'paid') {
            $query->where('price', '>', 0);
        } elseif ($request->input('price_filter') === 'free') {
            $query->where(function ($q) {
                $q->whereNull('price')->orWhere('price', '<=', 0);
            });
        }

        // Filter by published status
        if ($request->has('is_published') && $request->input('is_published') !== '') {
            if ((int) $request->input('is_published') === 1) {
                $query->whereNotNull('published_at');
            } else {
                $query->whereNull('published_at');
            }
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
        $posts = $query->paginate($perPage)->withQueryString();

        // Get categories and users for filters
        $categories = PostCategory::select('id', 'name')->orderBy('name')->get();
        $users = User::where('is_creator', true)
            ->select('id', 'name', 'login_name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'users' => $users,
            'filters' => $request->only([
                'search',
                'user_id',
                'category_id',
                'status',
                'type',
                'is_featured',
                'is_premium',
                'price_filter',
                'is_published',
                'date_from',
                'date_to',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): Response
    {
        $post->load(['user:id,name,login_name', 'category:id,name,slug']);

        $categories = PostCategory::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'post_category_id' => 'required|exists:post_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'type' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'free_after' => 'nullable|date',
            'published_at' => 'nullable|date',
        ]);

        // Convert checkbox values properly
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_premium'] = $request->boolean('is_premium');

        // Handle published_at logic
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        } elseif ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')->with('success', '帖子已更新');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', '帖子已删除');
    }
}
