<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json($plans);
    }

    public function show($slug)
    {
        $plan = Plan::where('slug', $slug)
            ->active()
            ->firstOrFail();

        return response()->json($plan);
    }
}