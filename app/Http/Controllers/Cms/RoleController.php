<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('columns')->orderBy('id')->get();
        $stats = [
            'total' => $roles->count(),
            'system' => $roles->where('is_system', true)->count(),
            'custom' => $roles->where('is_system', false)->count(),
        ];

        return view('cms.pengguna.roles.index', compact('roles', 'stats'));
    }

    public function create()
    {
        $columnTypes = $this->getColumnTypes();
        $existingRoles = Role::with('columns')->get();
        return view('cms.pengguna.roles.create', compact('columnTypes', 'existingRoles'));
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

        DB::transaction(function () use ($request, $data) {
            $role = Role::create($data);

            // Create table automatically
            $this->createRoleTable($data['table_name']);

            // Create default columns
            $this->generateDefaultColumns($role, $request->input('columns', []));

            // Generate model file
            $this->generateRoleModel($data['relation_name'], $data['table_name']);
        });

        return redirect()
            ->route('cms.pengguna.roles.index')
            ->with('success', __('cms.roles.created_successfully'));
    }

    public function edit(Role $role)
    {
        $columnTypes = $this->getColumnTypes();

        // Auto-sync table columns to role_columns so existing DB tables are reflected in the form
        $this->syncTableColumnsToRole($role);

        $role->load('columns');
        return view('cms.pengguna.roles.edit', compact('role', 'columnTypes'));
    }

    /**
     * Manually trigger column sync for a role (called from UI button).
     * Returns JSON for AJAX requests, redirect otherwise.
     */
    public function triggerSync(Role $role, Request $request)
    {
        $this->syncTableColumnsToRole($role);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('cms.roles.columns_synced'),
                'columns_count' => $role->fresh()->columns->count(),
            ]);
        }

        return redirect()
            ->route('cms.pengguna.roles.edit', $role)
            ->with('success', __('cms.roles.columns_synced'));
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

        $oldTableName = $role->table_name;
        $role->update($data);

        // Rename table if table_name changed (DDL - outside transaction)
        if ($oldTableName !== $data['table_name'] && Schema::hasTable($oldTableName)) {
            Schema::rename($oldTableName, $data['table_name']);
        }

        // Sync columns (contains DDL - must run outside DB::transaction)
        $this->syncColumns($role, $request->input('columns', []));

        // Update model file if relation_name changed
        $this->generateRoleModel($data['relation_name'], $data['table_name']);

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

        // Drop table
        if (Schema::hasTable($role->table_name)) {
            Schema::dropIfExists($role->table_name);
        }

        // Delete model file
        $modelPath = app_path('Models/' . ucfirst($role->relation_name) . '.php');
        if (file_exists($modelPath)) {
            unlink($modelPath);
        }

        $role->delete();

        return redirect()
            ->route('cms.pengguna.roles.index')
            ->with('success', __('cms.roles.deleted_successfully'));
    }

    /**
     * Sync existing DB table columns to role_columns for a given role.
     * Stores EXACT MySQL DATA_TYPE from INFORMATION_SCHEMA (varchar, int, datetime, enum, etc.)
     * to match phpMyAdmin exactly.
     */
    private function syncTableColumnsToRole(Role $role): void
    {
        if (!Schema::hasTable($role->table_name)) {
            return;
        }

        $dbName = config('database.connections.mysql.database');

        $columns = DB::select("
            SELECT
                COLUMN_NAME,
                DATA_TYPE,
                CHARACTER_MAXIMUM_LENGTH,
                NUMERIC_PRECISION,
                NUMERIC_SCALE,
                IS_NULLABLE,
                COLUMN_TYPE,
                COLUMN_DEFAULT,
                COLUMN_COMMENT
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$dbName, $role->table_name]);

        // Get unique index columns for this table
        $uniqueIndexColumns = DB::select("
            SELECT COLUMN_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND NON_UNIQUE = 0 AND INDEX_NAME != 'PRIMARY'
        ", [$dbName, $role->table_name]);
        $uniqueColumns = array_column($uniqueIndexColumns, 'COLUMN_NAME');

        $skipColumns = ['id', 'user_id', 'created_at', 'updated_at'];
        $sortOrder = 0;

        foreach ($columns as $column) {
            $columnName = $column->COLUMN_NAME;

            if (in_array($columnName, $skipColumns)) {
                continue;
            }

            // Store EXACT MySQL DATA_TYPE (varchar, int, datetime, enum, etc.)
            $exactDbType = strtolower($column->DATA_TYPE);

            // Determine length based on type
            // Only types that actually accept a length parameter in MySQL should have a length value.
            // Types like TEXT, BLOB, DATE, DATETIME, FLOAT, DOUBLE do NOT take a length.
            $typesWithoutLength = ['text', 'longtext', 'mediumtext', 'tinytext', 'blob', 'longblob', 'mediumblob', 'date', 'datetime', 'timestamp', 'time', 'float', 'double', 'enum', 'set'];
            $columnLength = null;

            if (!in_array($exactDbType, $typesWithoutLength)) {
                if ($column->CHARACTER_MAXIMUM_LENGTH !== null) {
                    $columnLength = (int) $column->CHARACTER_MAXIMUM_LENGTH;
                } elseif (in_array($exactDbType, ['decimal', 'numeric']) && $column->NUMERIC_PRECISION !== null) {
                    $columnLength = (int) $column->NUMERIC_PRECISION;
                }

                // For types like int(11), varchar(255), extract from COLUMN_TYPE if length still null
                if ($columnLength === null && $column->COLUMN_TYPE) {
                    if (preg_match('/\((\d+)(?:,(\d+))?\)/', $column->COLUMN_TYPE, $matches)) {
                        $columnLength = (int) $matches[1];
                    }
                }
            }

            $isNullable = $column->IS_NULLABLE === 'YES';
            $defaultValue = $column->COLUMN_DEFAULT;

            // Extract enum options from COLUMN_TYPE (e.g., enum('a','b','c'))
            $options = null;
            if (in_array($exactDbType, ['enum', 'set']) && $column->COLUMN_TYPE) {
                $options = $this->extractEnumOptions($column->COLUMN_TYPE);
            }

            $exists = RoleColumn::where('role_id', $role->id)
                ->where('column_name', $columnName)
                ->first();

            $data = [
                'column_name' => $columnName,
                'column_type' => $exactDbType, // EXACT MySQL type: varchar, int, datetime, enum, etc.
                'column_label' => ucwords(str_replace('_', ' ', $columnName)),
                'column_length' => $columnLength,
                'is_nullable' => $isNullable,
                'is_unique' => in_array($columnName, $uniqueColumns),
                'default_value' => $defaultValue,
                'options' => $options,
                'sort_order' => $sortOrder++,
            ];

            if ($exists) {
                $exists->update($data);
            } else {
                RoleColumn::create(['role_id' => $role->id, ...$data]);
            }
        }

        // Regenerate model so fillable stays in sync
        $this->generateRoleModel($role->relation_name, $role->table_name);
    }

    private function extractEnumOptions(string $columnType): array
    {
        if (preg_match("/^enum\\((.*)\\)$/i", $columnType, $matches) || preg_match("/^set\\((.*)\\)$/i", $columnType, $matches)) {
            $options = [];
            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $matches[1], $values);
            foreach ($values[1] as $value) {
                $options[] = stripslashes($value);
            }
            return $options;
        }
        return [];
    }

    private function mapDbTypeToRoleType(string $dbType): string
    {
        return match (strtolower($dbType)) {
            'varchar', 'char', 'text', 'longtext', 'mediumtext', 'tinytext' => 'string',
            'int', 'bigint', 'smallint', 'tinyint', 'mediumint', 'integer' => 'integer',
            'decimal', 'float', 'double', 'numeric' => 'decimal',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'enum', 'set' => 'enum',
            'boolean', 'tinyint(1)' => 'boolean',
            'blob', 'longblob', 'mediumblob' => 'file',
            default => 'string',
        };
    }

    private function extractColumnLength(?string $dbType): ?int
    {
        if (!$dbType) return null;
        if (preg_match('/\((\d+)\)/', $dbType, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Get available column types — matches EXACT MySQL DATA_TYPE names
     * (varchar, int, datetime, enum, etc.) to match phpMyAdmin exactly.
     */
    private function getColumnTypes(): array
    {
        return [
            'varchar'    => 'VARCHAR',
            'char'       => 'CHAR',
            'text'       => 'TEXT',
            'longtext'   => 'LONGTEXT',
            'mediumtext' => 'MEDIUMTEXT',
            'tinytext'   => 'TINYTEXT',
            'int'        => 'INT',
            'bigint'     => 'BIGINT',
            'smallint'   => 'SMALLINT',
            'tinyint'    => 'TINYINT',
            'mediumint'  => 'MEDIUMINT',
            'decimal'    => 'DECIMAL',
            'float'      => 'FLOAT',
            'double'     => 'DOUBLE',
            'date'       => 'DATE',
            'datetime'   => 'DATETIME',
            'timestamp'  => 'TIMESTAMP',
            'time'       => 'TIME',
            'enum'       => 'ENUM',
            'set'        => 'SET',
            'boolean'    => 'BOOLEAN',
            'blob'       => 'BLOB',
            'longblob'   => 'LONGBLOB',
            'mediumblob' => 'MEDIUMBLOB',
        ];
    }

    /**
     * Create the role profile table dynamically.
     */
    private function createRoleTable(string $tableName): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Generate default columns for a role.
     */
    private function generateDefaultColumns(Role $role, array $columnsInput): void
    {
        // If columns provided from form, use them
        if (!empty($columnsInput)) {
            $this->syncColumns($role, $columnsInput);
            return;
        }

        // Otherwise, create minimal default columns
        $defaults = [
            ['column_name' => 'nomor_kartu_identitas', 'column_type' => 'string', 'column_length' => 25, 'column_label' => 'Nomor Kartu Identitas', 'is_nullable' => true, 'is_unique' => true],
            ['column_name' => 'alamat', 'column_type' => 'text', 'column_length' => null, 'column_label' => 'Alamat', 'is_nullable' => true, 'is_unique' => false],
            ['column_name' => 'nomor_whatsapp', 'column_type' => 'string', 'column_length' => 20, 'column_label' => 'Nomor WhatsApp', 'is_nullable' => true, 'is_unique' => true],
        ];

        foreach ($defaults as $index => $col) {
            $column = RoleColumn::create([
                'role_id' => $role->id,
                'sort_order' => $index,
                ...$col,
            ]);

            $this->addColumnToTable($role->table_name, $column);
        }
    }

    /**
     * Sync columns for a role.
     */
    private function syncColumns(Role $role, array $columnsInput): void
    {
        $existingIds = $role->columns->pluck('id')->toArray();
        $updatedIds = [];

        foreach ($columnsInput as $index => $input) {
            $columnData = [
                'column_name' => $input['column_name'],
                'column_type' => $input['column_type'],
                'column_label' => $input['column_label'],
                'column_length' => $input['column_length'] ?? null,
                'is_nullable' => ($input['is_nullable'] ?? '0') == '1',
                'is_unique' => ($input['is_unique'] ?? '0') == '1',
                'default_value' => $input['default_value'] ?? null,
                'options' => !empty($input['options']) ? explode(',', $input['options']) : null,
                'sort_order' => $index,
            ];

            if (!empty($input['id'])) {
                // Update existing
                $column = RoleColumn::find($input['id']);
                if ($column) {
                    $oldName = $column->column_name;
                    $column->update($columnData);
                    $this->alterColumn($role->table_name, $oldName, $column);
                    $updatedIds[] = $column->id;
                }
            } else {
                // Create new
                $column = RoleColumn::create(['role_id' => $role->id, ...$columnData]);
                $this->addColumnToTable($role->table_name, $column);
                $updatedIds[] = $column->id;
            }
        }

        // Delete removed columns
        $toDelete = array_diff($existingIds, $updatedIds);
        foreach ($toDelete as $id) {
            $column = RoleColumn::find($id);
            if ($column) {
                $this->dropColumnFromTable($role->table_name, $column->column_name);
                $column->delete();
            }
        }
    }

    /**
     * Add a column to the database table.
     */
    private function addColumnToTable(string $tableName, RoleColumn $column): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, $column->column_name)) {
            return;
        }

        Schema::table($tableName, function ($table) use ($column) {
            $this->defineColumn($table, $column, 'add');
        });
    }

    /**
     * Alter an existing column in the database table.
     */
    private function alterColumn(string $tableName, string $oldName, RoleColumn $column): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, $oldName)) {
            $this->addColumnToTable($tableName, $column);
            return;
        }

        // For simple rename or type change, we need to use raw SQL for MySQL
        $this->modifyColumn($tableName, $oldName, $column);
    }

    /**
     * Define column structure for Blueprint using EXACT MySQL types.
     */
    private function defineColumn($table, RoleColumn $column, string $action = 'add')
    {
        $method = $action === 'add' ? 'addColumn' : 'column';
        $type = strtolower($column->column_type);

        $col = match ($type) {
            'varchar'  => $table->string($column->column_name, $column->column_length ?? 255),
            'char'     => $table->char($column->column_name, $column->column_length ?? 255),
            'text'     => $table->text($column->column_name),
            'longtext' => $table->longText($column->column_name),
            'mediumtext' => $table->mediumText($column->column_name),
            'tinytext' => $table->tinyText($column->column_name),
            'int'      => $table->integer($column->column_name),
            'bigint'   => $table->bigInteger($column->column_name),
            'smallint' => $table->smallInteger($column->column_name),
            'tinyint'  => $table->tinyInteger($column->column_name),
            'mediumint' => $table->mediumInteger($column->column_name),
            'decimal'  => $table->decimal($column->column_name, $column->column_length ?? 10, 2),
            'float'    => $table->float($column->column_name),
            'double'   => $table->double($column->column_name),
            'date'     => $table->date($column->column_name),
            'datetime' => $table->dateTime($column->column_name),
            'timestamp'=> $table->timestamp($column->column_name),
            'time'     => $table->time($column->column_name),
            'enum'     => $table->enum($column->column_name, $column->options ?? ['option_1']),
            'set'      => $table->set($column->column_name, $column->options ?? ['option_1']),
            'boolean'  => $table->boolean($column->column_name),
            'blob'     => $table->binary($column->column_name),
            'longblob' => $table->binary($column->column_name),
            'mediumblob' => $table->binary($column->column_name),
            // Backwards compatibility with old mapped types
            'string'   => $table->string($column->column_name, $column->column_length ?? 255),
            'integer'  => $table->integer($column->column_name),
            'file'     => $table->string($column->column_name, 255),
            default    => $table->string($column->column_name, 255),
        };

        if ($column->is_nullable) {
            $col->nullable();
        }

        if ($column->default_value !== null && $column->default_value !== '') {
            $col->default($column->default_value);
        }

        return $col;
    }

    /**
     * Modify column using raw SQL for complex changes.
     * Uses EXACT MySQL types from role_columns.
     */
    private function modifyColumn(string $tableName, string $oldName, RoleColumn $column): void
    {
        $type = $this->buildMysqlTypeDefinition($column);
        $nullable = $column->is_nullable ? "NULL" : "NOT NULL";
        $default = '';

        if ($column->default_value !== null && $column->default_value !== '') {
            $default = "DEFAULT '{$column->default_value}'";
        }

        try {
            if ($oldName !== $column->column_name) {
                // Rename column
                DB::statement("ALTER TABLE `{$tableName}` CHANGE `{$oldName}` `{$column->column_name}` {$type} {$nullable} {$default}");
            } else {
                // Modify column
                DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$column->column_name}` {$type} {$nullable} {$default}");
            }
        } catch (\Exception $e) {
            // Ignore errors for incompatible type changes
        }
    }

    /**
     * Build exact MySQL type definition string (e.g., varchar(255), int(11), decimal(10,2)).
     */
    private function buildMysqlTypeDefinition(RoleColumn $column): string
    {
        $type = strtolower($column->column_type);
        $length = $column->column_length;

        return match ($type) {
            'varchar'    => "VARCHAR({$length})",
            'char'       => "CHAR({$length})",
            'text'       => "TEXT",
            'longtext'   => "LONGTEXT",
            'mediumtext' => "MEDIUMTEXT",
            'tinytext'   => "TINYTEXT",
            'int'        => "INT" . ($length ? "({$length})" : ""),
            'bigint'     => "BIGINT" . ($length ? "({$length})" : ""),
            'smallint'   => "SMALLINT" . ($length ? "({$length})" : ""),
            'tinyint'    => "TINYINT" . ($length ? "({$length})" : ""),
            'mediumint'  => "MEDIUMINT" . ($length ? "({$length})" : ""),
            'decimal'    => "DECIMAL({$length},2)",
            'float'      => "FLOAT",
            'double'     => "DOUBLE",
            'date'       => "DATE",
            'datetime'   => "DATETIME",
            'timestamp'  => "TIMESTAMP",
            'time'       => "TIME",
            'enum'       => $column->options
                ? "ENUM(" . implode(',', array_map(fn($o) => "'{$o}'", $column->options)) . ")"
                : "ENUM('option_1')",
            'set'        => $column->options
                ? "SET(" . implode(',', array_map(fn($o) => "'{$o}'", $column->options)) . ")"
                : "SET('option_1')",
            'boolean'    => "TINYINT(1)",
            'blob'       => "BLOB",
            'longblob'   => "LONGBLOB",
            'mediumblob' => "MEDIUMBLOB",
            // Backwards compatibility
            'string'     => "VARCHAR({$length})",
            'integer'    => "INT",
            'file'       => "VARCHAR(255)",
            default      => "VARCHAR(255)",
        };
    }

    /**
     * Drop a column from the database table.
     */
    private function dropColumnFromTable(string $tableName, string $columnName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function ($table) use ($columnName) {
            $table->dropColumn($columnName);
        });
    }

    /**
     * Generate model file for the role.
     */
    private function generateRoleModel(string $relationName, string $tableName): void
    {
        $modelName = ucfirst($relationName);
        $modelPath = app_path("Models/{$modelName}.php");

        // Get columns from role_columns table
        $role = Role::where('relation_name', $relationName)->first();
        $fillable = ["'user_id'"];

        if ($role) {
            $columns = $role->columns->pluck('column_name')->toArray();
            foreach ($columns as $col) {
                $fillable[] = "'{$col}'";
            }
        }

        $fillableStr = implode(",\n        ", $fillable);

        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    protected \$table = '{$tableName}';

    protected \$fillable = [
        {$fillableStr}
    ];

    public function user()
    {
        return \$this->belongsTo(User::class);
    }
}
PHP;

        file_put_contents($modelPath, $content);
    }
}
