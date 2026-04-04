<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            'admin' => redirect()->route('dashboard.admin'),
            'pegawai' => redirect()->route('dashboard.pegawai'),
            'pelajar_mahasiswa' => redirect()->route('dashboard.pelajar'),
            'instansi_swasta' => redirect()->route('dashboard.instansi'),
            default => redirect()->route('dashboard.umum'),
        };
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }

    public function pegawai(): View
    {
        return view('dashboards.pegawai');
    }

    public function umum(): View
    {
        return view('dashboards.umum');
    }

    public function pelajar(): View
    {
        return view('dashboards.pelajar');
    }

    public function instansi(): View
    {
        return view('dashboards.instansi');
    }
}
