<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('badge_color', 50)->nullable()->after('is_registerable');
        });

        // Seed default badge colors
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('name', 'admin')
            ->update(['badge_color' => 'red']);
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('name', 'pegawai')
            ->update(['badge_color' => 'yellow']);
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('name', 'umum')
            ->update(['badge_color' => 'gray']);
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('name', 'pelajar_mahasiswa')
            ->update(['badge_color' => 'blue']);
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('name', 'instansi_swasta')
            ->update(['badge_color' => 'purple']);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('badge_color');
        });
    }
};
