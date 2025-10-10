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
        Schema::table('users', function (Blueprint $table) {
            // Referral system
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            
            // User roles and status
            $table->boolean('is_admin')->default(false)->after('parent_id');
            $table->boolean('is_creator')->default(false)->after('is_admin');
            $table->boolean('is_verified')->default(false)->after('is_creator');
            $table->boolean('is_top_creator')->default(false)->after('is_verified');
            $table->tinyInteger('type')->default(1)->after('is_top_creator')->comment('1=regular, 2=premium');
            $table->tinyInteger('status')->default(1)->after('type')->comment('1=active, 2=suspended, 3=banned');
            
            // Login and profile
            $table->string('login_name', 32)->nullable()->unique()->after('status');
            $table->tinyInteger('sex')->nullable()->after('name')->comment('1=male, 2=female, 3=other');
            $table->date('date_birth')->nullable()->after('sex');
            $table->string('phone', 16)->nullable()->unique()->after('date_birth');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            
            // Gamification and economy
            $table->unsignedInteger('xp')->default(0)->after('phone_verified_at');
            $table->unsignedInteger('points')->default(0)->after('xp');
            $table->decimal('credits', 10, 2)->default(0)->after('points');
            $table->decimal('balance', 10, 2)->default(0)->after('credits');
            
            // Social metrics (denormalized for performance)
            $table->unsignedInteger('followers_count')->default(0)->after('balance');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
            
            // Profile details
            $table->string('avatar', 255)->nullable()->after('following_count');
            $table->string('signature', 255)->nullable()->after('avatar');
            $table->text('description')->nullable()->after('avatar');
            
            // Login tracking
            $table->string('last_login_ip', 45)->nullable()->after('description');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
            $table->string('last_login_user_agent', 255)->nullable()->after('last_login_at');
            
            // Referral code
            $table->string('referral_code', 32)->nullable()->unique()->after('last_login_user_agent');
            
            // Indexes for better performance
            $table->index('status');
            $table->index('is_creator');
            $table->index('is_verified');
            $table->index('xp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['parent_id']);
            
            // Drop indexes
            $table->dropIndex(['status']);
            $table->dropIndex(['is_creator']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['xp']);
            
            // Drop all added columns
            $table->dropColumn([
                'parent_id',
                'is_admin',
                'is_creator',
                'is_verified',
                'is_top_creator',
                'type',
                'status',
                'login_name',
                'sex',
                'date_birth',
                'phone',
                'phone_verified_at',
                'xp',
                'points',
                'credits',
                'balance',
                'followers_count',
                'following_count',
                'avatar',
                'signature',
                'description',
                'last_login_ip',
                'last_login_at',
                'last_login_user_agent',
                'referral_code',
            ]);
        });
    }
};
