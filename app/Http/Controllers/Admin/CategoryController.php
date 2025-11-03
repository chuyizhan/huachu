<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search and filters.
     */
    public function index(Request $request): Response
    {
        $query = PostCategory::query()->withCount('posts');

        // Search by name, slug, or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', (int) $request->input('is_active'));
        }

        // Filter by nav item status
        if ($request->has('is_nav_item') && $request->input('is_nav_item') !== '') {
            $query->where('is_nav_item', (int) $request->input('is_nav_item'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $categories = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only([
                'search',
                'is_active',
                'is_nav_item',
                'sort_by',
                'sort_direction',
                'per_page',
            ]),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:post_categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'required|integer',
            'is_active' => 'boolean',
            'is_nav_item' => 'boolean',
            'nav_route' => 'nullable|string|max:255',
        ]);

        // Convert checkbox values properly
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_nav_item'] = $request->boolean('is_nav_item', false);

        // Clear nav_route if is_nav_item is false
        if (!$validated['is_nav_item']) {
            $validated['nav_route'] = null;
        }

        // Handle icon image upload
        if ($request->hasFile('icon_image')) {
            $iconImage = $request->file('icon_image');
            $iconPath = $iconImage->store('categories/icons', 'public');
            $validated['icon_image'] = $iconPath;
        }

        // Handle category image upload
        if ($request->hasFile('category_image')) {
            $categoryImage = $request->file('category_image');
            $categoryPath = $categoryImage->store('categories/images', 'public');
            $validated['category_image'] = $categoryPath;
        }

        PostCategory::create($validated);

        return redirect()->route('admin.categories.index')->with('success', '分类已创建');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(PostCategory $category): Response
    {
        $category->loadCount('posts');

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Display the specified category.
     */
    public function show(PostCategory $category): Response
    {
        $category->loadCount('posts');

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, PostCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:post_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_icon_image' => 'boolean',
            'remove_category_image' => 'boolean',
            'sort_order' => 'required|integer',
            'is_active' => 'boolean',
            'is_nav_item' => 'boolean',
            'nav_route' => 'nullable|string|max:255',
        ]);

        // Convert checkbox values properly
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_nav_item'] = $request->boolean('is_nav_item');

        // Clear nav_route if is_nav_item is false
        if (!$validated['is_nav_item']) {
            $validated['nav_route'] = null;
        }

        // Handle icon image upload or removal
        if ($request->boolean('remove_icon_image')) {
            // User wants to remove the icon image
            if ($category->icon_image && \Storage::disk('public')->exists($category->icon_image)) {
                \Storage::disk('public')->delete($category->icon_image);
            }
            $validated['icon_image'] = null;
        } elseif ($request->hasFile('icon_image')) {
            // User uploaded a new icon image
            if ($category->icon_image && \Storage::disk('public')->exists($category->icon_image)) {
                \Storage::disk('public')->delete($category->icon_image);
            }
            $iconImage = $request->file('icon_image');
            $iconPath = $iconImage->store('categories/icons', 'public');
            $validated['icon_image'] = $iconPath;
        } else {
            // No change to icon image, don't update
            unset($validated['icon_image']);
        }

        // Handle category image upload or removal
        if ($request->boolean('remove_category_image')) {
            // User wants to remove the category image
            if ($category->category_image && \Storage::disk('public')->exists($category->category_image)) {
                \Storage::disk('public')->delete($category->category_image);
            }
            $validated['category_image'] = null;
        } elseif ($request->hasFile('category_image')) {
            // User uploaded a new category image
            if ($category->category_image && \Storage::disk('public')->exists($category->category_image)) {
                \Storage::disk('public')->delete($category->category_image);
            }
            $categoryImage = $request->file('category_image');
            $categoryPath = $categoryImage->store('categories/images', 'public');
            $validated['category_image'] = $categoryPath;
        } else {
            // No change to category image, don't update
            unset($validated['category_image']);
        }

        // Remove the removal flags from validated data
        unset($validated['remove_icon_image']);
        unset($validated['remove_category_image']);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', '分类已更新');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(PostCategory $category)
    {
        if ($category->posts()->count() > 0) {
            return redirect()->back()->with('error', '无法删除有关联帖子的分类');
        }

        // Delete associated images
        if ($category->icon_image && \Storage::disk('public')->exists($category->icon_image)) {
            \Storage::disk('public')->delete($category->icon_image);
        }
        if ($category->category_image && \Storage::disk('public')->exists($category->category_image)) {
            \Storage::disk('public')->delete($category->category_image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', '分类已删除');
    }
}
