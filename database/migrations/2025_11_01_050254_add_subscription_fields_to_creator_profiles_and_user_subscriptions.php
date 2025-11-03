<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add platform commission rate to creator profiles if not exists
        if (!Schema::hasColumn('creator_profiles', 'platform_commission_rate')) {
            Schema::table('creator_profiles', function (Blueprint $table) {
                $table->decimal('platform_commission_rate', 5, 2)->default(30.00)->after('review_count')
                    ->comment('Platform revenue share percentage (default 30%)');
            });
        }

        // Add revenue tracking fields to user subscriptions
        if (!Schema::hasColumn('user_subscriptions', 'creator_subscription_plan_id')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->foreignId('creator_subscription_plan_id')->nullable()->after('plan_id')
                    ->constrained('creator_subscription_plans')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('user_subscriptions', 'platform_amount')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->decimal('platform_amount', 10, 2)->nullable()->after('amount')
                    ->comment('Platform commission amount');
            });
        }

        if (!Schema::hasColumn('user_subscriptions', 'creator_amount')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->decimal('creator_amount', 10, 2)->nullable()->after('platform_amount')
                    ->comment('Amount creator receives');
            });
        }

        // Add index
        try {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->index(['creator_id', 'status', 'expires_at']);
            });
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['creator_id', 'status', 'expires_at']);
            $table->dropForeign(['creator_subscription_plan_id']);
            $table->dropColumn(['creator_subscription_plan_id', 'platform_amount', 'creator_amount']);
        });

        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->dropColumn('platform_commission_rate');
        });
    }
};
