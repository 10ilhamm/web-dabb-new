<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationStatusController extends Controller
{
    /**
     * Display the verification status page.
     */
    public function show(Request $request): View
    {
        $status = $request->query('status', 'unknown');

        $data = match ($status) {
            'success' => [
                'title' => 'Verifikasi Email Berhasil!',
                'message' => 'Alamat email Anda telah berhasil diverifikasi. Sekarang Anda dapat mengakses semua fitur aplikasi.',
                'icon' => '✓',
                'type' => 'success',
            ],
            'already_verified' => [
                'title' => 'Email Sudah Terverifikasi',
                'message' => 'Alamat email Anda sudah pernah diverifikasi sebelumnya.',
                'icon' => 'ℹ',
                'type' => 'info',
            ],
            'expired' => [
                'title' => 'Link Verifikasi Kadaluarsa',
                'message' => 'Link verifikasi yang Anda gunakan sudah kadaluarsa. Silakan minta link verifikasi baru.',
                'icon' => '⏰',
                'type' => 'warning',
            ],
            'invalid' => [
                'title' => 'Verifikasi Gagal',
                'message' => 'Link verifikasi tidak valid. Silakan coba lagi atau minta link verifikasi baru.',
                'icon' => '✗',
                'type' => 'error',
            ],
            default => [
                'title' => 'Status Verifikasi',
                'message' => 'Terjadi kesalahan. Silakan hubungi administrator.',
                'icon' => '?',
                'type' => 'error',
            ],
        };

        return view('auth.verification-status', $data);
    }
}