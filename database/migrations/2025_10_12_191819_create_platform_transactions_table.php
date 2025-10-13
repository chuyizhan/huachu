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
        Schema::create('platform_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_purchase_id')->constrained('post_purchases')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('Platform commission amount');
            $table->decimal('percentage', 5, 2)->comment('Commission percentage applied');
            $table->decimal('creator_amount', 10, 2)->comment('Amount paid to creator');
            $table->decimal('total_transaction', 10, 2)->comment('Total transaction amount');
            $table->text('metadata')->nullable()->comment('Additional transaction metadata');
            $table->timestamps();

            $table->index(['creator_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_transactions');
    }
};
