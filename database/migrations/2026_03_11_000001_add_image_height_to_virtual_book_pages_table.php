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
            $table->integer('image_height')->default(50)->comment('Image height percentage (10-100)')->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->dropColumn('image_height');
        });
    }
};
