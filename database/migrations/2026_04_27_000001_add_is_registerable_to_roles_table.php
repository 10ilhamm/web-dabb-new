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
        Schema::table('roles', function (Blueprint $table): void {
            $table->boolean('is_registerable')->default(false)->after('is_system');
        });

        // Seed default registerable roles
        \Illuminate\Support\Facades\DB::table('roles')
            ->whereIn('name', ['umum', 'pelajar_mahasiswa', 'instansi_swasta'])
            ->update(['is_registerable' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('is_registerable');
        });
    }
};