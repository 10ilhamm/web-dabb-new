<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPelajar extends Model
{
    protected $table = 'user_pelajars';

    protected $fillable = [
        'user_id',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kartu_identitas',
        'nomor_kartu_identitas',
        'alamat',
        'nomor_whatsapp',
        'jenis_keperluan',
        'judul_keperluan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
