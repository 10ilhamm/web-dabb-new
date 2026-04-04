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
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            // Rename old image to images_json (will store as JSON array)
            $table->json('images')->nullable()->after('image');
            $table->json('image_positions')->nullable()->after('images');
            $table->json('text_position')->nullable()->after('image_positions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->dropColumn(['images', 'image_positions', 'text_position']);
        });
    }
};
