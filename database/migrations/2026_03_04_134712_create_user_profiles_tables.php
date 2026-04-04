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
        Schema::create('user_umums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('kartu_identitas'); // path to uploaded file
            $table->string('nomor_kartu_identitas', 25)->unique();
            $table->text('alamat');
            $table->string('nomor_whatsapp', 20)->unique();
            $table->string('jenis_keperluan');
            $table->string('judul_keperluan');
            $table->timestamps();
        });

        Schema::create('user_pelajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('kartu_identitas'); // path to KTM/kartu pelajar
            $table->string('nomor_kartu_identitas', 25)->unique();
            $table->text('alamat');
            $table->string('nomor_whatsapp', 20)->unique();
            $table->string('jenis_keperluan');
            $table->string('judul_keperluan');
            $table->timestamps();
        });

        Schema::create('user_instansis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Instansi doesn't have jenis kelamin but uses nama in users table as nama instansi
            $table->string('tempat_lahir', 100); // Usually referring to user perwakilan or established place if it matches UI exactly
            $table->date('tanggal_lahir');
            $table->string('kartu_identitas'); // path to identitas instansi / perwakilan
            $table->string('nomor_kartu_identitas', 25)->unique();
            $table->text('alamat');
            $table->string('nomor_whatsapp', 20)->unique();
            $table->string('jenis_keperluan');
            $table->string('judul_keperluan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles_tables');
    }
};
