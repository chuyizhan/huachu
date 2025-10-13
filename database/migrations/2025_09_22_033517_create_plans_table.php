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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->default(0);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('period_days')->comment('Plan duration in days');
            $table->json('features')->nullable(); // Array of features
            $table->integer('max_premium_posts')->nullable(); // null = unlimited
            $table->boolean('can_create_premium_content')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->string('badge_color', 7)->default('#fbbf24');
            $table->string('badge_text_color', 7)->default('#000000');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
