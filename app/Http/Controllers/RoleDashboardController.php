<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $roleName = $request->user()->role;
        $roleModel = Role::where('name', $roleName)->first();

        if ($roleModel && $roleModel->dashboard_route) {
            return redirect()->route($roleModel->dashboard_route);
        }

        // Fallback: match by role name prefix
        return match ($roleName) {
            'admin' => redirect()->route('dashboard.admin'),
            'pegawai' => redirect()->route('dashboard.pegawai'),
            default => redirect()->route('dashboard.umum'),
        };
    }

    public function admin(): View { return view('dashboards.admin'); }
    public function pegawai(): View { return view('dashboards.pegawai'); }
    public function umum(): View { return view('dashboards.umum'); }
    public function pelajar(): View { return view('dashboards.pelajar'); }
    public function instansi(): View { return view('dashboards.instansi'); }
}
