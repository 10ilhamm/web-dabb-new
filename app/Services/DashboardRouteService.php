<?php

namespace App\Services;

use App\Models\Role;
use App\Http\Controllers\RoleDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class DashboardRouteService
{
    /**
     * Convert role name to URL slug.
     * Example: 'pelajar_mahasiswa' => 'pelajar-mahasiswa'
     */
    public static function toSlug(string $name): string
    {
        return Str::slug($name, '-');
    }

    /**
     * Convert URL slug back to role name.
     * Example: 'pelajar-mahasiswa' => 'pelajar_mahasiswa'
     */
    public static function fromSlug(string $slug): string
    {
        return Str::replace('-', '_', $slug);
    }

    /**
     * Build the route name from role name.
     * Example: 'pelajar_mahasiswa' => 'dashboard.pelajar-mahasiswa'
     */
    public static function buildRouteName(string $roleName): string
    {
        return 'dashboard.' . self::toSlug($roleName);
    }

    /**
     * Register all role dashboard routes dynamically from database.
     * Authorization is handled INSIDE the controller (not via middleware)
     * so that the same URL works for any authenticated user whose role matches.
     */
    public static function registerRoutes(): void
    {
        try {
            $roles = Role::all();
        } catch (Throwable) {
            return;
        }

        foreach ($roles as $role) {
            if (empty($role->dashboard_view)) {
                continue;
            }

            $slug = self::toSlug($role->name);
            $routeName = self::buildRouteName($role->name);

            if (Route::has($routeName)) {
                continue;
            }

            // No role middleware here — controller handles authorization
            Route::get("/dashboard/{$slug}", [RoleDashboardController::class, 'show'])
                ->name($routeName);
        }
    }

    /**
     * Get dashboard route name for a given role.
     * Always returns a valid route name.
     */
    public static function getRouteName(string $roleName): string
    {
        return self::buildRouteName($roleName);
    }

    /**
     * Get dashboard view for a given role.
     */
    public static function getView(string $roleName): ?string
    {
        try {
            $role = Role::where('name', $roleName)->first();
            return $role?->dashboard_view;
        } catch (Throwable) {
            return null;
        }
    }
}
