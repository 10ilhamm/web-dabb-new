<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'label' => 'Admin',
                'table_name' => 'user_admins',
                'relation_name' => 'userAdmin',
                'is_system' => true,
                'description' => 'Administrator dengan akses penuh ke CMS',
            ],
            [
                'name' => 'pegawai',
                'label' => 'Pegawai',
                'table_name' => 'user_pegawais',
                'relation_name' => 'userPegawai',
                'is_system' => true,
                'description' => 'Pegawai ANRI',
            ],
            [
                'name' => 'umum',
                'label' => 'Umum',
                'table_name' => 'user_umums',
                'relation_name' => 'userUmum',
                'is_system' => true,
                'description' => 'Pengunjung umum',
            ],
            [
                'name' => 'pelajar_mahasiswa',
                'label' => 'Pelajar / Mahasiswa',
                'table_name' => 'user_pelajars',
                'relation_name' => 'userPelajar',
                'is_system' => true,
                'description' => 'Pelajar atau mahasiswa',
            ],
            [
                'name' => 'instansi_swasta',
                'label' => 'Instansi / Swasta',
                'table_name' => 'user_instansis',
                'relation_name' => 'userInstansi',
                'is_system' => true,
                'description' => 'Instansi atau perusahaan swasta',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}

