<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    /**
     * Convert URL slug back to role name. e.g. 'pelajar-mahasiswa' => 'pelajar_mahasiswa'
     */
    private static function fromSlug(string $slug): string
    {
        return Str::replace('-', '_', $slug);
    }

    /**
     * Build route name from role name. e.g. 'pelajar_mahasiswa' => 'dashboard.pelajar-mahasiswa'
     */
    private static function buildRouteName(string $roleName): string
    {
        return 'dashboard.' . Str::slug($roleName, '-');
    }

    /**
     * Generic dashboard index — redirect to role-specific dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        $roleName = $request->user()->role;
        $slug = Str::slug($roleName, '-');

        return redirect()->route('dashboard.role', ['slug' => $slug]);
    }

    /**
     * Dynamic dashboard view — resolves view from roles.dashboard_view.
     * Passes role metadata to the view so the blade is fully dynamic.
     */
    public function show(Request $request, string $roleIdentifier): View
    {
        // Convert slug to actual role name
        $roleName = self::fromSlug($roleIdentifier);

        // Security: user can only view their own role's dashboard
        if ($request->user()->role !== $roleName) {
            abort(403, 'Anda tidak memiliki akses ke dashboard ini.');
        }

        $roleModel = Role::where('name', $roleName)->first();

        if (!$roleModel) {
            abort(404, 'Role tidak ditemukan.');
        }

        // Build dynamic data passed to the blade view
        $viewData = [
            'role' => $roleName,
            'roleLabel' => $roleModel->label,
            'badgeColor' => $roleModel->badge_color,
        ];

        // Resolve view — if dashboard_view is set, use it; otherwise use dynamic
        $viewPath = $roleModel->dashboard_view;

        if ($viewPath) {
            return view($viewPath, $viewData);
        }

        // Fallback: use dynamic dashboard template
        return view('dashboards.index', $viewData);
    }
}
