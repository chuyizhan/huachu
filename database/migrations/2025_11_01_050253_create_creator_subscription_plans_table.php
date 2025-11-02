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
        Schema::create('creator_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->comment('e.g., Weekly, Monthly, Quarterly');
            $table->text('description')->nullable();
            $table->integer('duration_days')->comment('Subscription duration in days');
            $table->decimal('price', 10, 2)->comment('Price in 金币 (credits)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            $table->index(['creator_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_subscription_plans');
    }
};
