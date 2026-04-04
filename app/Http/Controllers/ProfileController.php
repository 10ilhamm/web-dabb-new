<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile view.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's password edit form.
     */
    public function password(Request $request): View
    {
        return view('profile.password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's login activity.
     */
    public function activity(Request $request): View
    {
        $sessions = collect();

        if (config('session.driver') === 'database') {
            $sessions = DB::table('sessions')
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
                ->map(function ($session) use ($request) {
                    $agent = $this->createAgent($session->user_agent);

                    return (object) [
                        'agent' => $agent,
                        'ip_address' => $session->ip_address,
                        'is_current_device' => $session->id === $request->session()->getId(),
                        'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    ];
                });
        }

        return view('profile.activity', [
            'user' => $request->user(),
            'sessions' => $sessions,
        ]);
    }

    /**
     * Parse the user agent string into OS and Browser.
     */
    protected function createAgent($userAgent)
    {
        $platform = 'Unknown';
        $browser = 'Unknown';
        $isDesktop = true;

        if (! $userAgent) {
            return (object) compact('platform', 'browser', 'isDesktop');
        }

        // Platform
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'AndroidOS';
            $isDesktop = false;
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $isDesktop = false;
        }

        // Browser
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/opr|opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'IE';
        }

        return (object) compact('platform', 'browser', 'isDesktop');
    }

    /**
     * Log out other browser sessions.
     */
    public function logoutOtherBrowserSessions(Request $request): RedirectResponse
    {
        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return Redirect::route('profile.activity')->with('status', 'browser-sessions-terminated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $userData = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ];

            if ($request->hasFile('photo')) {
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('profile-photos', 'public');
            }

            $user->fill($userData);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            // Handle Profile Data
            if ($request->hasFile('kartu_identitas')) {
                if ($user->profile && $user->profile->kartu_identitas) {
                    Storage::disk('public')->delete($user->profile->kartu_identitas);
                }
                $data['kartu_identitas'] = $request->file('kartu_identitas')->store('kartu-identitas', 'public');
            }

            $profileFields = ['nip', 'nomor_whatsapp', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'jabatan', 'pangkat_golongan', 'alamat', 'nomor_kartu_identitas', 'jenis_keperluan', 'judul_keperluan', 'kartu_identitas'];
            $profileData = [];

            foreach ($profileFields as $field) {
                if (array_key_exists($field, $data)) {
                    $profileData[$field] = $data[$field];
                }
            }

            if (!empty($profileData)) {
                if ($user->profile) {
                    // Ensure we don't try to save a field that doesn't exist on this particular profile
                    $validProfileData = array_intersect_key($profileData, array_flip(app()->make(get_class($user->profile))->getFillable() ?: $profileFields));
                    
                    // Fast workaround since some fillable might not be set in models, we can just do a direct update:
                    $user->profile->update($profileData);
                } else {
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
                        case 'admin':
                            $user->userAdmin()->create($profileData);
                            break;
                        case 'pegawai':
                            $user->userPegawai()->create($profileData);
                            break;
                    }
                }
            }

            return Redirect::route('profile.show')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            return Redirect::route('profile.edit')->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
