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
        Schema::create('creator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('specialty')->nullable(); // e.g., "Culinary Arts", "Photography", etc.
            $table->integer('experience_years')->default(0);
            $table->json('certifications')->nullable(); // Array of certifications
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // Instagram, YouTube, etc.
            $table->string('portfolio_url')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('follower_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00); // Average rating out of 5
            $table->integer('review_count')->default(0);
            $table->timestamps();

            $table->index(['verification_status', 'is_featured']);
            $table->index(['specialty', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_profiles');
    }
};
