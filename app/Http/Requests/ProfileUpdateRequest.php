<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'photo' => ['nullable', 'image', 'max:2048'],
            'nip' => ['nullable', 'string', 'max:18'],
            'nomor_whatsapp' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable'], // will be handled below
            'agama' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:80'],
            'pangkat_golongan' => ['nullable', 'string', 'max:80'],
            'alamat' => ['nullable', 'string'],
            'kartu_identitas' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'nomor_kartu_identitas' => ['nullable', 'string', 'max:50'],
            'jenis_keperluan' => ['nullable', 'string', 'max:255'],
            'judul_keperluan' => ['nullable', 'string', 'max:255'],
        ];

        $tableName = match($this->user()->role) {
            'admin' => 'user_admins',
            'pegawai' => 'user_pegawais',
            'umum' => 'user_umums',
            'pelajar_mahasiswa' => 'user_pelajars',
            default => 'user_umums'
        };

        if ($this->user()->role !== 'instansi_swasta') {
            $jkList = \App\Models\User::getEnumValues($tableName, 'jenis_kelamin');
            $rules['jenis_kelamin'] = ['nullable', 'in:' . implode(',', $jkList)];
        }

        if (in_array($this->user()->role, ['admin', 'pegawai'])) {
            $tableName = $this->user()->role === 'admin' ? 'user_admins' : 'user_pegawais';
            $agamaList = \App\Models\User::getEnumValues($tableName, 'agama');
            $jabatanList = \App\Models\User::getEnumValues($tableName, 'jabatan');
            $pangkatList = \App\Models\User::getEnumValues($tableName, 'pangkat_golongan');

            $rules['agama'] = ['nullable', 'in:' . implode(',', $agamaList)];
            $rules['jabatan'] = ['nullable', 'in:' . implode(',', $jabatanList)];
            $rules['pangkat_golongan'] = ['nullable', 'in:' . implode(',', $pangkatList)];
        }

        return $rules;
    }
}
