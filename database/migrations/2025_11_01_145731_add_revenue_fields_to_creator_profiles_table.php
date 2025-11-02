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
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->decimal('total_earnings', 15, 2)->default(0)->after('platform_commission_rate')
                ->comment('Total revenue earned from subscriptions (creator share)');
            $table->decimal('total_platform_share', 15, 2)->default(0)->after('total_earnings')
                ->comment('Total platform commission from subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creator_profiles', function (Blueprint $table) {
            $table->dropColumn(['total_earnings', 'total_platform_share']);
        });
    }
};
