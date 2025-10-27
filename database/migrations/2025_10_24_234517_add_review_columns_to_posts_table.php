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
            // Review status: pending, approved, rejected
            $table->string('review_status')->default('pending')->after('status');

            // Who reviewed it
            $table->foreignId('reviewed_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();

            // When it was reviewed
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            // Optional: Review notes/reason
            $table->text('review_notes')->nullable()->after('reviewed_at');

            // Add index for filtering
            $table->index('review_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['review_status']);
            $table->dropColumn(['review_status', 'reviewed_by', 'reviewed_at', 'review_notes']);
        });
    }
};
