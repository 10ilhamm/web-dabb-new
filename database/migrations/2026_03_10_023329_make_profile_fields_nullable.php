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
        Schema::table('user_umums', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan'])->nullable()->change();
            $table->string('tempat_lahir', 100)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('kartu_identitas')->nullable()->change();
            $table->string('nomor_kartu_identitas', 25)->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('nomor_whatsapp', 20)->nullable()->change();
        });

        Schema::table('user_pelajars', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan'])->nullable()->change();
            $table->string('tempat_lahir', 100)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('kartu_identitas')->nullable()->change();
            $table->string('nomor_kartu_identitas', 25)->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('nomor_whatsapp', 20)->nullable()->change();
        });

        Schema::table('user_instansis', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('kartu_identitas')->nullable()->change();
            $table->string('nomor_kartu_identitas', 25)->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('nomor_whatsapp', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not necessary for this fix
    }
};
