<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_slideshow_slides', function (Blueprint $table) {
            $table->foreignId('feature_page_id')
                ->nullable()
                ->constrained('feature_pages')
                ->onDelete('cascade')
                ->after('feature_id');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_slideshow_slides', function (Blueprint $table) {
            $table->dropForeign(['feature_page_id']);
            $table->dropColumn('feature_page_id');
        });
    }
};
