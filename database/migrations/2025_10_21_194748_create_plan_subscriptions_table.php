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
        Schema::create('plan_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('subscription_number')->unique()->comment('Unique subscription identifier');
            $table->enum('status', ['active', 'cancelled', 'expired', 'suspended', 'trial'])->default('active');
            $table->decimal('price_paid', 10, 2)->comment('Price paid at subscription time');
            $table->string('payment_method')->nullable()->comment('Payment method used');
            $table->string('payment_transaction_id')->nullable()->comment('External payment reference');
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable()->comment('Subscription expiration date');
            $table->timestamp('trial_ends_at')->nullable()->comment('Trial period end date');
            $table->timestamp('cancelled_at')->nullable()->comment('Cancellation date');
            $table->text('cancellation_reason')->nullable()->comment('Reason for cancellation');
            $table->boolean('auto_renew')->default(true)->comment('Auto-renewal enabled');
            $table->timestamp('last_renewed_at')->nullable()->comment('Last renewal date');
            $table->integer('renewal_count')->default(0)->comment('Number of renewals');
            $table->json('metadata')->nullable()->comment('Additional subscription data');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['plan_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('started_at');
            $table->index('subscription_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_subscriptions');
    }
};
