<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(10)
                    ->mixedCase()
                    ->symbols(),
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Set the user's password if it is currently null (e.g. registered via Google).
     */
    public function set(Request $request): RedirectResponse
    {
        if ($request->user()->password !== null) {
            return back()->with('error', 'Password has already been set.');
        }

        $validated = $request->validateWithBag('setPassword', [
            'role' => ['required', 'in:umum,pelajar_mahasiswa,instansi_swasta'],
            'jenis_keperluan' => ['required', 'string', 'max:255'],
            'judul_keperluan' => ['required', 'string', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                Password::min(10)
                    ->mixedCase()
                    ->symbols(),
            ],
        ]);

        $user = $request->user();
        $user->update([
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        $profileData = [
            'jenis_keperluan' => $validated['jenis_keperluan'],
            'judul_keperluan' => $validated['judul_keperluan'],
        ];

        switch ($user->role) {
            case 'umum':
                $user->userUmum()->create($profileData);
                break;
            case 'pelajar_mahasiswa':
                $user->userPelajar()->create($profileData);
                break;
            case 'instansi_swasta':
                $user->userInstansi()->create($profileData);
                break;
        }

        return redirect()->route('profile.show')->with('info', __('Silakan lengkapi data diri Anda untuk melanjutkan.'));
    }
}
