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
        Schema::table('users', function (Blueprint $table): void {
            // Change role from enum to string to allow dynamic roles
            $table->string('role')->default('umum')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Revert back to enum (data loss possible if new roles were added)
            $table->enum('role', ['admin', 'pegawai', 'umum', 'pelajar_mahasiswa', 'instansi_swasta'])
                ->default('umum')
                ->change();
        });
    }
};

