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
        Schema::table('features', function (Blueprint $table) {
            $table->boolean('is_virtual_book')->default(false)->after('content_en');
            $table->string('book_cover')->nullable()->after('is_virtual_book');
            $table->string('book_thumbnail')->nullable()->after('book_cover');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn(['is_virtual_book', 'book_cover', 'book_thumbnail']);
        });
    }
};
