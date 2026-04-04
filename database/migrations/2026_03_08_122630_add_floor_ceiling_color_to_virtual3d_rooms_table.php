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
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->string('floor_color')->default('#8B7355')->after('wall_color');
            $table->string('ceiling_color')->default('#f5f5f5')->after('floor_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->dropColumn(['floor_color', 'ceiling_color']);
        });
    }
};
