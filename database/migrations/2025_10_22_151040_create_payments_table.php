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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // Payment gateway information
            $table->string('payment_number')->unique()->comment('Internal payment tracking number');
            $table->string('trade_number')->nullable()->comment('Payment gateway trade number');
            $table->string('gateway')->default('third_party')->comment('Payment gateway name');
            $table->string('payment_method')->comment('alipay, wechat, bank, etc');

            // Amount details
            $table->decimal('amount', 10, 2)->comment('Payment amount');
            $table->decimal('fee', 10, 2)->default(0)->comment('Payment gateway fee');
            $table->decimal('actual_amount', 10, 2)->comment('Actual amount received');

            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('currency', 3)->default('CNY');

            // User information
            $table->string('payer_ip', 45)->nullable()->comment('Payer IP address');
            $table->string('payer_name')->nullable();
            $table->string('payer_account')->nullable()->comment('Payer account (masked)');

            // Gateway response data
            $table->text('request_data')->nullable()->comment('JSON of request sent to gateway');
            $table->text('response_data')->nullable()->comment('JSON of gateway response');
            $table->text('callback_data')->nullable()->comment('JSON of callback data');

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index('payment_number');
            $table->index('trade_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
