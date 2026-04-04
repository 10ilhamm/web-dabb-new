<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->foreignId('book_id')->nullable()->constrained()->onDelete('cascade')->after('feature_id');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_book_pages', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropColumn('book_id');
        });
    }
};
