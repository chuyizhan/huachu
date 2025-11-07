<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PostCategory;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->get('parent');
        $parentCategory = null;

        if ($parentId) {
            // Viewing a parent category - show its children
            $parentCategory = PostCategory::with('parent')->findOrFail($parentId);

            $categories = PostCategory::active()
                ->where('parent_id', $parentId)
                ->withCount(['posts' => function($query) {
                    $query->published()->approved();
                }])
                ->orderBy('sort_order')
                ->get();
        } else {
            // Show only top-level categories (no parent)
            $categories = PostCategory::active()
                ->parents()
                ->with(['children' => function($query) {
                    $query->active()->orderBy('sort_order');
                }])
                ->withCount(['posts' => function($query) {
                    $query->published()->approved();
                }])
                ->orderBy('sort_order')
                ->get();
        }

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'parentCategory' => $parentCategory,
        ]);
    }
}
