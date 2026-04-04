<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('back_title')->nullable()->after('title_position');
            $table->string('back_cover_image')->nullable()->after('back_title');
            $table->json('back_cover_position')->nullable()->after('back_cover_image');
            $table->float('back_cover_scale', 8, 2)->default(1.00)->after('back_cover_position');
            $table->json('back_title_position')->nullable()->after('back_cover_scale');
            $table->json('back_cover_texts')->nullable()->after('back_title_position');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'back_title',
                'back_cover_image',
                'back_cover_position',
                'back_cover_scale',
                'back_title_position',
                'back_cover_texts',
            ]);
        });
    }
};
