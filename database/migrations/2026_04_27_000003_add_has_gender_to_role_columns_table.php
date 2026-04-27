<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_columns', function (Blueprint $table): void {
            $table->boolean('has_gender')->default(false)->after('is_nullable');
        });

        // Seed: enable has_gender for jenis_kelamin column in umum and pelajar_mahasiswa roles
        $genderRoleIds = \Illuminate\Support\Facades\DB::table('roles')
            ->whereIn('name', ['umum', 'pelajar_mahasiswa'])
            ->pluck('id');

        \Illuminate\Support\Facades\DB::table('role_columns')
            ->whereIn('role_id', $genderRoleIds)
            ->where('column_name', 'jenis_kelamin')
            ->update(['has_gender' => true]);
    }

    public function down(): void
    {
        Schema::table('role_columns', function (Blueprint $table): void {
            $table->dropColumn('has_gender');
        });
    }
};