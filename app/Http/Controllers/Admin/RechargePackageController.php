<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RechargePackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RechargePackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $packages = RechargePackage::orderBy('sort_order')
            ->orderBy('amount')
            ->get();

        return Inertia::render('Admin/RechargePackages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/RechargePackages/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:999999',
            'bonus' => 'required|numeric|min:0|max:999999',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        RechargePackage::create($validated);

        return redirect()->route('admin.recharge-packages.index')
            ->with('success', '充值套餐创建成功！');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RechargePackage $rechargePackage): Response
    {
        return Inertia::render('Admin/RechargePackages/Edit', [
            'package' => $rechargePackage,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RechargePackage $rechargePackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:999999',
            'bonus' => 'required|numeric|min:0|max:999999',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        $rechargePackage->update($validated);

        return redirect()->route('admin.recharge-packages.index')
            ->with('success', '充值套餐更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RechargePackage $rechargePackage)
    {
        $rechargePackage->delete();

        return redirect()->route('admin.recharge-packages.index')
            ->with('success', '充值套餐删除成功！');
    }
}
