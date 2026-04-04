<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPegawai extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kartu_identitas',
        'nomor_kartu_identitas',
        'alamat',
        'nomor_whatsapp',
        'agama',
        'jabatan',
        'pangkat_golongan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
