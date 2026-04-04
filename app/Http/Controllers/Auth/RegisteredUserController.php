<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Dasar untuk Tabel Users
        $rules = [
            'role' => ['required', 'in:admin,pegawai,umum,pelajar_mahasiswa,instansi_swasta'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // 2. Validasi Dinamis Berdasarkan Role
        if ($request->role === 'umum') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'jenis_kelamin' => ['required', 'in:Laki-Laki,Perempuan'],
                'tempat_lahir' => ['required', 'string', 'max:100'],
                'tanggal_lahir' => ['required', 'date'],
                'kartu_identitas' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'nomor_kartu_identitas' => ['required', 'string', 'max:25', 'unique:user_umums,nomor_kartu_identitas'],
                'alamat' => ['required', 'string'],
                'nomor_whatsapp' => ['required', 'string', 'max:20', 'unique:user_umums,nomor_whatsapp'],
                'jenis_keperluan' => ['required', 'string', 'max:255'],
                'judul_keperluan' => ['required', 'string', 'max:255'],
            ]);
        } elseif ($request->role === 'pelajar_mahasiswa') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'jenis_kelamin' => ['required', 'in:Laki-Laki,Perempuan'],
                'tempat_lahir' => ['required', 'string', 'max:100'],
                'tanggal_lahir' => ['required', 'date'],
                'kartu_identitas' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'nomor_kartu_identitas' => ['required', 'string', 'max:25', 'unique:user_pelajars,nomor_kartu_identitas'],
                'alamat' => ['required', 'string'],
                'nomor_whatsapp' => ['required', 'string', 'max:20', 'unique:user_pelajars,nomor_whatsapp'],
                'jenis_keperluan' => ['required', 'string', 'max:255'],
                'judul_keperluan' => ['required', 'string', 'max:255'],
            ]);
        } elseif ($request->role === 'instansi_swasta') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255'], // Nama Instansi/Perusahaan
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'tempat_lahir' => ['required', 'string', 'max:100'],
                'tanggal_lahir' => ['required', 'date'],
                'kartu_identitas' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'nomor_kartu_identitas' => ['required', 'string', 'max:25', 'unique:user_instansis,nomor_kartu_identitas'],
                'alamat' => ['required', 'string'],
                'nomor_whatsapp' => ['required', 'string', 'max:20', 'unique:user_instansis,nomor_whatsapp'],
                'jenis_keperluan' => ['required', 'string', 'max:255'],
                'judul_keperluan' => ['required', 'string', 'max:255'],
            ]);
        } else {
            // Default for admin / pegawai
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        // Upload File if exists
        $kartuIdentitasPath = null;
        if ($request->hasFile('kartu_identitas')) {
            $kartuIdentitasPath = $request->file('kartu_identitas')->store('identitas', 'public');
        }

        // 3. Simpan User Utama
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // nullable for admin/pegawai
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // 4. Simpan Profil Berdasarkan Role
        if ($request->role === 'umum') {
            $user->userUmum()->create([
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'kartu_identitas' => $kartuIdentitasPath,
                'nomor_kartu_identitas' => $request->nomor_kartu_identitas,
                'alamat' => $request->alamat,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'jenis_keperluan' => $request->jenis_keperluan,
                'judul_keperluan' => $request->judul_keperluan,
            ]);
        } elseif ($request->role === 'pelajar_mahasiswa') {
            $user->userPelajar()->create([
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'kartu_identitas' => $kartuIdentitasPath,
                'nomor_kartu_identitas' => $request->nomor_kartu_identitas,
                'alamat' => $request->alamat,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'jenis_keperluan' => $request->jenis_keperluan,
                'judul_keperluan' => $request->judul_keperluan,
            ]);
        } elseif ($request->role === 'instansi_swasta') {
            $user->userInstansi()->create([
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'kartu_identitas' => $kartuIdentitasPath,
                'nomor_kartu_identitas' => $request->nomor_kartu_identitas,
                'alamat' => $request->alamat,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'jenis_keperluan' => $request->jenis_keperluan,
                'judul_keperluan' => $request->judul_keperluan,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
