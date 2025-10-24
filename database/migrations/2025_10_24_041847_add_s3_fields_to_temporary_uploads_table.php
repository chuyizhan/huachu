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
        Schema::table('temporary_uploads', function (Blueprint $table) {
            $table->string('s3_path')->nullable()->after('type');
            $table->string('original_name')->nullable()->after('s3_path');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->bigInteger('size')->nullable()->after('mime_type');
            $table->boolean('confirmed')->default(false)->after('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table) {
            $table->dropColumn(['s3_path', 'original_name', 'mime_type', 'size', 'confirmed']);
        });
    }
};
