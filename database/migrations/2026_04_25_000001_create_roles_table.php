<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique(); // internal key: admin, pegawai, etc.
            $table->string('label'); // display name
            $table->string('table_name')->nullable(); // profile table name, e.g. user_admins
            $table->string('relation_name')->nullable(); // Eloquent relation method on User model, e.g. userAdmin
            $table->boolean('is_system')->default(false); // prevent deletion of core roles
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default roles
        $now = now();
        $roles = [
            ['name' => 'admin', 'label' => 'Administrator', 'table_name' => 'user_admins', 'relation_name' => 'userAdmin', 'is_system' => true, 'description' => 'Akun administrator dengan akses penuh ke CMS.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pegawai', 'label' => 'Pegawai', 'table_name' => 'user_pegawais', 'relation_name' => 'userPegawai', 'is_system' => true, 'description' => 'Akun pegawai ANRI.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'umum', 'label' => 'Umum', 'table_name' => 'user_umums', 'relation_name' => 'userUmum', 'is_system' => true, 'description' => 'Pengunjung umum.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pelajar_mahasiswa', 'label' => 'Pelajar / Mahasiswa', 'table_name' => 'user_pelajars', 'relation_name' => 'userPelajar', 'is_system' => true, 'description' => 'Pelajar atau mahasiswa yang melakukan penelitian.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'instansi_swasta', 'label' => 'Instansi / Swasta', 'table_name' => 'user_instansis', 'relation_name' => 'userInstansi', 'is_system' => true, 'description' => 'Perwakilan instansi atau swasta.', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('roles')->insert($roles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

