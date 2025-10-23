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
        // Change the status enum to include 'pending'
        DB::statement("ALTER TABLE plan_subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'cancelled', 'expired', 'suspended', 'trial') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum without 'pending'
        DB::statement("ALTER TABLE plan_subscriptions MODIFY COLUMN status ENUM('active', 'cancelled', 'expired', 'suspended', 'trial') NOT NULL DEFAULT 'active'");
    }
};
