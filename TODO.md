# TODO: Dynamic Role Management

## Step 1: Database

- [x] Migration: `create_roles_table.php`
- [x] Migration: `change_users_role_to_string.php`
- [x] Seeder: `RoleSeeder.php` (populate existing roles)

## Step 2: Model

- [x] `app/Models/Role.php`
- [x] Update `app/Models/User.php` (`roleLabels()`, `getProfileAttribute()`, add `role()` relation)

## Step 3: Controller

- [x] `app/Http/Controllers/Cms/RoleController.php`
- [x] Update `app/Http/Controllers/Cms/PenggunaController.php` (use dynamic roles)

## Step 4: Routes

- [x] Update `routes/web.php` (add role CRUD routes under cms/pengguna)

## Step 5: Views

- [x] `resources/views/cms/pengguna/roles/index.blade.php`
- [x] `resources/views/cms/pengguna/roles/create.blade.php`
- [x] `resources/views/cms/pengguna/roles/edit.blade.php`
- [x] Update `resources/views/cms/pengguna/page/index.blade.php` (add link to role management)

## Step 6: Translations

- [x] Update `resources/lang/id/cms.php`
- [x] Update `resources/lang/en/cms.php`

## Step 7: Test

- [x] Run migrations
- [x] Verify role CRUD works
- [x] Verify user management still works with dynamic roles

## Step 8: Auto NPM Build Command

- [x] Create `app/Console/Commands/AutoNpmBuild.php` — artisan command `npm:build` with smart file change detection
- [x] Update `routes/console.php` — schedule `npm:build` every 5 minutes alongside `db:dump`
- [x] Update `tailwind.config.js` — add `safelist` patterns for all dynamic color classes (bg-, hover:bg-, text-, border-)

## Step 9: Role Form Enhancements

- [x] Update `resources/views/cms/pengguna/roles/create.blade.php` — change name help text to amber warning about lowercase/underscore-only
- [x] Update `resources/views/cms/pengguna/roles/edit.blade.php` — same warning styling
- [x] Update `resources/lang/id/cms.php` — add `form_name_warning` key
- [x] Update `resources/lang/en/cms.php` — add `form_name_warning` key

## Step 10: Role Type (is_system) in Forms

- [x] Update `app/Http/Controllers/Cms/RoleController.php` — validate `is_system` in store/update, remove forced `false`
- [x] Update `resources/views/cms/pengguna/roles/create.blade.php` — add `is_system` radio group (System/Custom)
- [x] Update `resources/views/cms/pengguna/roles/edit.blade.php` — add `is_system` radio group with prefill
- [x] Update `resources/views/cms/pengguna/roles/index.blade.php` — hide delete button for system roles
- [x] Update `resources/lang/id/cms.php` — add `form_type` and `form_type_help` keys
- [x] Update `resources/lang/en/cms.php` — add `form_type` and `form_type_help` keys

## Step 11: Profile Data in User Create/Edit

- [x] Update `resources/views/cms/pengguna/create.blade.php` — add role-specific profile sections with data-role-section wrappers
- [x] Update `resources/views/cms/pengguna/edit.blade.php` — add role-specific profile sections with prefill from `$profile`
- [x] Create `resources/views/cms/pengguna/_profile_fields.blade.php` — shared partial for admin & pegawai (NIP, jenis_kelamin, tempat_lahir, tanggal_lahir, kartu_identitas_file, nomor_kartu_identitas, alamat, nomor_whatsapp, agama, jabatan, pangkat_golongan)
- [x] Create `resources/views/cms/pengguna/_profile_fields_umum_pelajar.blade.php` — shared partial for umum & pelajar_mahasiswa (jenis_kelamin, tempat_lahir, tanggal_lahir, kartu_identitas_file, nomor_kartu_identitas, alamat, nomor_whatsapp, jenis_keperluan, judul_keperluan)
- [x] Create `resources/views/cms/pengguna/_profile_fields_instansi.blade.php` — partial for instansi_swasta (tanpa jenis_kelamin, dengan jenis_keperluan & judul_keperluan)
- [x] Update `public/js/cms/features/pengguna/create.js` — add `updateRoleSections()` toggle handler on role select change
- [x] Update `public/js/cms/features/pengguna/edit.js` — same toggle handler, call on init to show current role section
