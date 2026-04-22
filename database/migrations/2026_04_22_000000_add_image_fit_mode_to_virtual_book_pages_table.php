<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->string('image_fit_mode', 20)->default('contained')->after('image_height');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->dropColumn('image_fit_mode');
        });
    }
};
