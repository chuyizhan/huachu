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
        Schema::table('post_categories', function (Blueprint $table) {
            $table->integer('points_reward')->default(0)->after('is_active')->comment('Points awarded for publishing a post in this category');
            $table->integer('minimum_images')->default(0)->after('points_reward')->comment('Minimum images required to earn points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_categories', function (Blueprint $table) {
            $table->dropColumn(['points_reward', 'minimum_images']);
        });
    }
};
