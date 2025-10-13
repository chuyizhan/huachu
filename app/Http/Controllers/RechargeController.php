<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CreditTransaction;
use App\Models\RechargePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RechargeController extends Controller
{
    /**
     * Show the recharge page with available packages
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Get active packages from database
        $packages = RechargePackage::active()
            ->ordered()
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'amount' => (float) $package->amount,
                    'bonus' => (float) $package->bonus,
                    'total' => $package->total,
                    'description' => $package->description,
                    'is_popular' => $package->is_popular,
                ];
            });

        return Inertia::render('Recharge/Index', [
            'packages' => $packages,
            'userCredits' => (float) $user->credits,
        ]);
    }

    /**
     * Process the recharge (fake payment)
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|exists:recharge_packages,id',
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|in:fake,alipay,wechat',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Get package details if it's a package purchase
            $package = null;
            $baseAmount = $validated['amount'];
            $bonus = 0;

            if (isset($validated['package_id'])) {
                $package = RechargePackage::find($validated['package_id']);
                if ($package) {
                    $baseAmount = (float) $package->amount;
                    $bonus = (float) $package->bonus;
                }
            }

            // Create order - only charge the base amount
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'type' => 'recharge',
                'amount' => $baseAmount,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_info' => [
                    'fake_payment' => true,
                    'processed_at' => now()->toDateTimeString(),
                    'package_id' => $package ? $package->id : null,
                    'bonus_credits' => $bonus,
                ],
            ]);

            // Simulate successful payment (fake)
            $order->markAsCompleted();

            // Add base amount credits to user
            $user->credits += $baseAmount;
            $user->save();

            // Create credit transaction for base amount (recharge)
            CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'earned',
                'credits' => $baseAmount,
                'reason' => 'recharge',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_method' => $validated['payment_method'],
                    'package_id' => $package ? $package->id : null,
                ],
                'related_type' => Order::class,
                'related_id' => $order->id,
            ]);

            // If there's a bonus, add it separately and create another transaction
            if ($bonus > 0) {
                $user->credits += $bonus;
                $user->save();

                CreditTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'earned',
                    'credits' => $bonus,
                    'reason' => 'recharge_bonus',
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'base_amount' => $baseAmount,
                    ],
                    'related_type' => Order::class,
                    'related_id' => $order->id,
                ]);
            }

            DB::commit();

            return redirect()->route('recharge.success', ['order' => $order->id])
                ->with('success', '充值成功！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '充值失败，请重试');
        }
    }

    /**
     * Show recharge success page
     */
    public function success(Order $order): Response
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();

        return Inertia::render('Recharge/Success', [
            'order' => $order,
            'userCredits' => (float) $user->credits,
        ]);
    }
}
