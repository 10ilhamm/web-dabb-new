<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Pass dynamic role profile columns for each registerable role (from DB)
        $registerableRoles = Role::registerable()->get();
        $rolesData = [];

        foreach ($registerableRoles as $roleModel) {
            $profileColumns = $roleModel->profileColumns();
            $enumOptions = [];

            foreach ($profileColumns as $col) {
                if (in_array($col->column_type, ['enum', 'set'])) {
                    $enumOptions[$col->column_name] = !empty($col->options)
                        ? $col->options
                        : User::getEnumValues($roleModel->table_name, $col->column_name);
                }
            }

            $rolesData[$roleModel->name] = [
                'label' => $roleModel->label,
                'columns' => $profileColumns,
                'enumOptions' => $enumOptions,
            ];
        }

        return view('auth.register', [
            'rolesData' => $rolesData,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Base validation rules
        $rules = [
            'role' => ['required', 'string', Rule::in(Role::registerable()->pluck('name')->toArray())],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // 2. Dynamic validation based on role_columns
        $role = $request->role;
        $profileColumns = User::roleProfileColumns($role);

        foreach ($profileColumns as $col) {
            $field = $col->column_name;
            $type = $col->column_type;
            $length = $col->column_length;
            $isNullable = $col->is_nullable;

            // Skip user table fields (handled above)
            if (in_array($field, ['name', 'email', 'password', 'photo'])) {
                if ($field === 'name') {
                    $rules['name'] = ['required', 'string', 'max:255'];
                }
                continue;
            }

            // File field (kartu_identitas)
            if ($type === 'blob' || $field === 'kartu_identitas') {
                $rules[$field] = $isNullable
                    ? ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048']
                    : ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
                continue;
            }

            // Enum/set fields
            if (in_array($type, ['enum', 'set'])) {
                $options = [];
                if (!empty($col->options)) {
                    $options = $col->options;
                } else {
                    $roleModel = Role::where('name', $role)->first();
                    if ($roleModel) {
                        $options = User::getEnumValues($roleModel->table_name, $field);
                    }
                }
                $rules[$field] = $isNullable
                    ? ['nullable', Rule::in($options)]
                    : ['required', Rule::in($options)];
                continue;
            }

            // Generate rule based on column type
            $rules[$field] = match ($type) {
                'varchar', 'char' => $isNullable
                    ? ['nullable', 'string', 'max:' . ($length ?? 255)]
                    : ['required', 'string', 'max:' . ($length ?? 255)],
                'text' => $isNullable ? ['nullable', 'string'] : ['required', 'string'],
                'int', 'bigint', 'smallint', 'tinyint' => $isNullable
                    ? ['nullable', 'integer']
                    : ['required', 'integer'],
                'decimal', 'float', 'double' => $isNullable
                    ? ['nullable', 'numeric']
                    : ['required', 'numeric'],
                'date' => $isNullable
                    ? ['nullable', 'date']
                    : ['required', 'date'],
                'datetime', 'timestamp' => $isNullable
                    ? ['nullable', 'date']
                    : ['required', 'date'],
                default => $isNullable
                    ? ['nullable', 'string']
                    : ['required', 'string'],
            };
        }

        $request->validate($rules);

        // 3. Upload file fields
        $profileData = [];
        $roleModel = Role::where('name', $role)->first();

        if ($roleModel) {
            $fileColumns = $roleModel->profileColumns()->filter(fn($c) => $c->column_type === 'blob');
            foreach ($fileColumns as $col) {
                $fieldName = $col->column_name;
                if ($request->hasFile($fieldName)) {
                    $profileData[$fieldName] = $request->file($fieldName)->store('identitas', 'public');
                }
            }

            // 4. Collect profile data from role_columns
            $profileColumnNames = $profileColumns->pluck('column_name')->toArray();
            foreach ($profileColumnNames as $field) {
                if (in_array($field, ['name', 'email', 'password', 'photo', 'kartu_identitas'])) {
                    continue;
                }
                if ($request->has($field)) {
                    $profileData[$field] = $request->input($field);
                }
            }
        }

        // 5. Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'password' => Hash::make($request->password),
        ]);

        // 6. Create profile dynamically
        if ($roleModel && $roleModel->relation_name) {
            $relation = $roleModel->relation_name;
            if (method_exists($user, $relation)) {
                $user->{$relation}()->create($profileData);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}