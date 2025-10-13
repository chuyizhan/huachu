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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Drop old foreign key if exists
            $table->dropForeign(['vip_tier_id']);

            // Rename column
            $table->renameColumn('vip_tier_id', 'plan_id');

            // Make creator_id nullable
            $table->unsignedBigInteger('creator_id')->nullable()->change();

            // Drop billing_cycle column
            $table->dropColumn('billing_cycle');

            // Update type enum values
            $table->enum('type', ['creator', 'plan'])->change();
        });

        // Add new foreign key
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            $table->index(['plan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Drop new foreign key
            $table->dropForeign(['plan_id']);
            $table->dropIndex(['plan_id', 'status']);

            // Rename back
            $table->renameColumn('plan_id', 'vip_tier_id');

            // Add back billing_cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->after('amount');

            // Restore type enum
            $table->enum('type', ['creator', 'tier'])->change();

            // Make creator_id not nullable
            $table->unsignedBigInteger('creator_id')->nullable(false)->change();
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->foreign('vip_tier_id')->references('id')->on('plans')->onDelete('set null');
        });
    }
};
