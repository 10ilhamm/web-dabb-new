# TODO: Role Database Schema Management

## Step 1: Database & Models

- [x] Create migration for `role_columns` table
- [ ] Create `RoleColumn` model
- [ ] Update `Role` model with `columns()` relation

## Step 2: Controller Logic

- [ ] Update `RoleController@index` — load columns count
- [ ] Update `RoleController@store` — auto-generate default columns + create DB table
- [ ] Update `RoleController@update` — sync column changes + alter DB table
- [ ] Update `RoleController@destroy` — drop table + delete columns
- [ ] Add `syncColumns()`, `getTableColumns()`, `generateDefaultColumns()`, `alterTable()` helpers

## Step 3: Views

- [ ] Update `roles/index.blade.php` — expandable columns detail
- [ ] Update `roles/create.blade.php` — dynamic column form
- [ ] Update `roles/edit.blade.php` — edit existing columns

## Step 4: Language

- [ ] Add role column keys to `id/cms.php` and `en/cms.php`

## Step 5: Testing

- [ ] Verify existing roles show columns
- [ ] Verify new role creates table with selected columns
- [ ] Verify edit role adds/removes columns and alters table
