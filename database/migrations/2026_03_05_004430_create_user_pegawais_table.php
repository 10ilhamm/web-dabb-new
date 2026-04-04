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
        Schema::create('user_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nip', 18)->unique();
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('kartu_identitas')->nullable(); // upload file
            $table->string('nomor_kartu_identitas', 25)->unique();
            $table->text('alamat');
            $table->string('nomor_whatsapp', 20)->unique();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
            $table->enum('jabatan', ['Kepala ANRI', 'Sekretaris Utama', 'Deputi Bidang Pembinaan Kearsipan', 'Deputi Bidang Informasi dan Pengembangan Sistem Kearsipan', 'Deputi Bidang Konservasi Arsip', 'Direktur Kearsipan Pusat', 'Direktur Kearsipan Daerah I & II', 'Direktur SDM Kearsipan dan Sertifikasi', 'Arsiparis Ahli Pertama', 'Arsiparis Ahli Muda', 'Arsiparis Ahli Madya', 'Arsiparis Ahli Utama', 'Arsiparis Terampil', 'Arsiparis Mahir', 'Arsiparis Penyelia', 'Konservator Arsip', 'Restorator Arsip', 'Reprogrator Arsip', 'Agendaris', 'Protokol', 'Sekretaris Pimpinan', 'Bendahara Gaji']);
            $table->enum('pangkat_golongan', ['IV/e (Pembina Utama)', 'IV/d (Pembina Utama Madya)', 'IV/c (Pembina Utama Muda)', 'IV/b (Pembina Tingkat I)', 'IV/a (Pembina)', 'III/d (Penata Tingkat I)', 'III/c (Penata)', 'III/b (Penata Muda Tingkat I)', 'III/a (Penata Muda)', 'II/d (Pengatur Tingkat I)', 'II/c (Pengatur)', 'II/b (Pengatur Muda Tingkat I)', 'II/a (Pengatur Muda)', 'I/d (Juru Tingkat I)', 'I/c (Juru)', 'I/b (Juru Muda Tingkat I)', 'I/a (Juru Muda)']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pegawais');
    }
};
