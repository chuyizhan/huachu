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
            $table->string('icon_image')->nullable()->after('icon')->comment('Path to category icon image');
            $table->string('category_image')->nullable()->after('icon_image')->comment('Path to category cover image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_categories', function (Blueprint $table) {
            $table->dropColumn(['icon_image', 'category_image']);
        });
    }
};
