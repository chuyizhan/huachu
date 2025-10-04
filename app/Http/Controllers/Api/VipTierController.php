<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VipTier;
use Illuminate\Http\Request;

class VipTierController extends Controller
{
    public function index(Request $request)
    {
        $tiers = VipTier::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json($tiers);
    }

    public function show($slug)
    {
        $tier = VipTier::where('slug', $slug)
            ->active()
            ->firstOrFail();

        return response()->json($tier);
    }
}