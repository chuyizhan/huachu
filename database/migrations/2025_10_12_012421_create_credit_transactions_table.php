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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earned', 'spent', 'deducted', 'refunded']);
            $table->decimal('credits', 10, 2); // Can be positive or negative
            $table->string('reason'); // e.g., 'purchase', 'refund', 'admin_adjustment'
            $table->json('metadata')->nullable(); // Additional context data
            $table->nullableMorphs('related'); // Related model (post, purchase, etc.)
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
