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
            // JSON field to store allowed user types: ['all', 'creator', 'regular', 'admin']
            // 'all' means everyone can post
            // 'creator' means only creators can post
            // 'regular' means only regular users (non-creators) can post
            // 'admin' means only admins can post
            // Can have multiple types e.g., ['creator', 'admin']
            $table->json('allowed_user_types')->nullable()->after('is_active');
        });

        // Set default value for existing rows
        DB::table('post_categories')->update([
            'allowed_user_types' => json_encode(['all'])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_categories', function (Blueprint $table) {
            $table->dropColumn('allowed_user_types');
        });
    }
};
