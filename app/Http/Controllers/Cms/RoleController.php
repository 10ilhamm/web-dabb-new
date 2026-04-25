<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id')->get();
        $stats = [
            'total' => $roles->count(),
            'system' => $roles->where('is_system', true)->count(),
            'custom' => $roles->where('is_system', false)->count(),
        ];

        return view('cms.pengguna.roles.index', compact('roles', 'stats'));
    }

    public function create()
    {
        return view('cms.pengguna.roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'label' => ['required', 'string', 'max:100'],
            'is_system' => ['required', 'boolean'],
            'table_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'table_name')],
            'relation_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-zA-Z0-9]*$/', Rule::unique('roles', 'relation_name')],
            'description' => ['nullable', 'string'],
        ], [
            'name.regex' => __('cms.roles.validation_name_regex'),
            'name.unique' => __('cms.roles.validation_name_unique'),
            'table_name.regex' => __('cms.roles.validation_table_name_regex'),
            'table_name.unique' => __('cms.roles.validation_table_name_unique'),
            'table_name.required' => __('cms.roles.validation_table_name_required'),
            'relation_name.regex' => __('cms.roles.validation_relation_name_regex'),
            'relation_name.unique' => __('cms.roles.validation_relation_name_unique'),
            'relation_name.required' => __('cms.roles.validation_relation_name_required'),
        ]);

        Role::create($data);

        return redirect()
            ->route('cms.pengguna.roles.index')
            ->with('success', __('cms.roles.created_successfully'));
    }

    public function edit(Role $role)
    {
        return view('cms.pengguna.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'label' => ['required', 'string', 'max:100'],
            'is_system' => ['required', 'boolean'],
            'table_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'table_name')->ignore($role->id)],
            'relation_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-zA-Z0-9]*$/', Rule::unique('roles', 'relation_name')->ignore($role->id)],
            'description' => ['nullable', 'string'],
        ], [
            'name.regex' => __('cms.roles.validation_name_regex'),
            'name.unique' => __('cms.roles.validation_name_unique'),
            'table_name.regex' => __('cms.roles.validation_table_name_regex'),
            'table_name.unique' => __('cms.roles.validation_table_name_unique'),
            'table_name.required' => __('cms.roles.validation_table_name_required'),
            'relation_name.regex' => __('cms.roles.validation_relation_name_regex'),
            'relation_name.unique' => __('cms.roles.validation_relation_name_unique'),
            'relation_name.required' => __('cms.roles.validation_relation_name_required'),
        ]);

        $role->update($data);

        return redirect()
            ->route('cms.pengguna.roles.index')
            ->with('success', __('cms.roles.updated_successfully'));
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()
                ->route('cms.pengguna.roles.index')
                ->with('error', __('cms.roles.cannot_delete_system'));
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()
                ->route('cms.pengguna.roles.index')
                ->with('error', __('cms.roles.cannot_delete_has_users', ['count' => $userCount]));
        }

        $role->delete();

        return redirect()
            ->route('cms.pengguna.roles.index')
            ->with('success', __('cms.roles.deleted_successfully'));
    }
}
