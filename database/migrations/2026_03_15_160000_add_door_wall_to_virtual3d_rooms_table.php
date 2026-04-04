<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->string('door_wall')->default('back')->after('door_link_type');
        });
    }

    public function down(): void
    {
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->dropColumn('door_wall');
        });
    }
};
