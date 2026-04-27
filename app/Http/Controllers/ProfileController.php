<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Role;
use App\Models\User;
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
        $user = $request->user();
        $profileColumns = User::roleProfileColumns($user->role);

        return view('profile.show', [
            'user' => $user,
            'profileColumns' => $profileColumns,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profileColumns = User::roleProfileColumns($user->role);

        // Pre-fetch enum options for enum-type columns
        $role = Role::where('name', $user->role)->first();
        $enumOptions = [];
        if ($role) {
            foreach ($profileColumns as $col) {
                if (in_array($col->column_type, ['enum', 'set'])) {
                    // Try to get options from role_columns first
                    if (!empty($col->options)) {
                        $enumOptions[$col->column_name] = $col->options;
                    } else {
                        // Fall back to actual DB enum values
                        $tableName = $role->table_name;
                        $enumOptions[$col->column_name] = User::getEnumValues($tableName, $col->column_name);
                    }
                }
            }
        }

        return view('profile.edit', [
            'user' => $user,
            'profileColumns' => $profileColumns,
            'enumOptions' => $enumOptions,
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

        return Redirect::route('profile.activity')->with('status', 'sessions-terminated');
    }

    /**
     * Update the user's profile information.
     * Dynamically handles all fields defined in role_columns.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            // 1. Update user table fields
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

            // 2. Handle profile data dynamically from role_columns
            $role = Role::where('name', $user->role)->first();
            if ($role) {
                $profileColumns = $role->profileColumns();
                $profileColumnNames = $profileColumns->pluck('column_name')->toArray();

                // Collect only fields that exist in role_columns
                $profileData = [];
                foreach ($profileColumnNames as $field) {
                    if (array_key_exists($field, $data)) {
                        $profileData[$field] = $data[$field];
                    }
                }

                // Handle kartu_identitas file upload
                if ($request->hasFile('kartu_identitas')) {
                    if ($user->profile && $user->profile->kartu_identitas) {
                        Storage::disk('public')->delete($user->profile->kartu_identitas);
                    }
                    $profileData['kartu_identitas'] = $request->file('kartu_identitas')->store('kartu-identitas', 'public');
                }

                if (!empty($profileData)) {
                    if ($user->profile) {
                        $user->profile->update($profileData);
                    } else {
                        $relation = $role->relation_name;
                        if (method_exists($user, $relation)) {
                            $user->{$relation}()->create($profileData);
                        }
                    }
                }
            }

            return Redirect::route('profile.show')->with('success', 'profile-updated');
        } catch (\Throwable $e) {
            return Redirect::route('profile.edit')
                ->withInput()
                ->with('error', 'profile-update-failed');
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

    /**
     * Send a fresh verification link to the user's email.
     */
    public function sendVerificationNotification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return Redirect::route('profile.show')
                ->with('status', 'already-verified');
        }

        $user->sendEmailVerificationNotification();

        return Redirect::route('profile.show')
            ->with('status', 'verification-sent');
    }
}
