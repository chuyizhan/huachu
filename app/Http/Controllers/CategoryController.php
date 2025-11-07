<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PostCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = PostCategory::active()
            ->withCount(['posts' => function($query) {
                $query->published()->approved();
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }
}
