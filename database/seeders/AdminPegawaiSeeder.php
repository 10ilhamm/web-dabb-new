<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAdmin;
use App\Models\UserPegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $adminUser = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@anri.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        UserAdmin::create([
            'user_id' => $adminUser->id,
            'nip' => '198001012005011001',
            'jenis_kelamin' => 'Laki-Laki',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1980-01-01',
            'kartu_identitas' => null,
            'nomor_kartu_identitas' => '3171234567890001',
            'alamat' => 'Jl. Ampera Raya No. 7, Jakarta Selatan',
            'nomor_whatsapp' => '081234567890',
            'agama' => 'Islam',
            'jabatan' => 'Kepala Subbagian Tata Usaha',
            'pangkat_golongan' => 'Penata Tk. I (III/d)',
        ]);

        // Create Pegawai
        $pegawaiUser = User::create([
            'name' => 'Pegawai Biasa',
            'username' => 'pegawai',
            'email' => 'pegawai@anri.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai', // Assuming 'pegawai' is the role name
        ]);

        UserPegawai::create([
            'user_id' => $pegawaiUser->id,
            'nip' => '199002022014022002',
            'jenis_kelamin' => 'Perempuan',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1990-02-02',
            'kartu_identitas' => null,
            'nomor_kartu_identitas' => '3271234567890002',
            'alamat' => 'Jl. Kebon Jeruk No. 8, Jakarta Barat',
            'nomor_whatsapp' => '081987654321',
            'agama' => 'Islam',
            'jabatan' => 'Arsiparis Ahli Pertama',
            'pangkat_golongan' => 'Penata Muda (III/a)',
        ]);
    }
}
