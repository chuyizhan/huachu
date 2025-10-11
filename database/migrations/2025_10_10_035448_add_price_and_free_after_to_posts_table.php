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
        Schema::table('posts', function (Blueprint $table) {
            // Price in credits to unlock this post (0 = free, null = not for sale)
            $table->decimal('price', 10, 2)->nullable()->after('is_premium')->comment('Price in credits to unlock (null = not for sale)');

            // When this post becomes free even if it has a price set
            $table->timestamp('free_after')->nullable()->after('price')->comment('Post becomes free after this datetime');

            // Index for querying paid posts
            $table->index('price');
            $table->index('free_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropIndex(['free_after']);
            $table->dropColumn(['price', 'free_after']);
        });
    }
};
