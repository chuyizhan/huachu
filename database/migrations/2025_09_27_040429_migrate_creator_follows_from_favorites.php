<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing creator follows from favorites to follows table
        $creatorFollows = DB::table('favorites')
            ->where('favoritable_type', 'App\\Models\\CreatorProfile')
            ->get();

        foreach ($creatorFollows as $follow) {
            DB::table('follows')->insert([
                'follower_id' => $follow->user_id,
                'creator_id' => $follow->favoritable_id,
                'created_at' => $follow->created_at,
                'updated_at' => $follow->updated_at,
            ]);
        }

        // Remove migrated records from favorites table
        DB::table('favorites')
            ->where('favoritable_type', 'App\\Models\\CreatorProfile')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move follows back to favorites table
        $follows = DB::table('follows')->get();

        foreach ($follows as $follow) {
            DB::table('favorites')->insert([
                'user_id' => $follow->follower_id,
                'favoritable_type' => 'App\\Models\\CreatorProfile',
                'favoritable_id' => $follow->creator_id,
                'created_at' => $follow->created_at,
                'updated_at' => $follow->updated_at,
            ]);
        }

        // Remove all records from follows table
        DB::table('follows')->delete();
    }
};
