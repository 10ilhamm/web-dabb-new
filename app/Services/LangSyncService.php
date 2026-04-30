<?php

namespace App\Services;

use App\Models\Role;
use App\Services\AutoLangService;
use Illuminate\Support\Facades\DB;

/**
 * LangSyncService — fully automatic, zero manual configuration.
 *
 * Uses Laravel's built-in __() function which already resolves correctly
 * in HTTP runtime. Enum DB values are mapped directly to auth.* lang keys.
 * New enum columns are auto-linked via MySQL SHOW COLUMNS + lang file matching.
 */
class LangSyncService
{
    /**
     * Static map: colName => [dbValue => langKey]
     * Built once from DB enum values + lang file matching.
     *
     * @var array<string, array<string, string>>
     */
    private static ?array $staticMap = null;

    /**
     * Get the auto-generated enum translation map.
     * Map format: $map[colName][dbValue] = langKey
     *
     * @return array<string, array<string, string>>
     */
    public static function getEnumTransMap(): array
    {
        if (self::$staticMap !== null) {
            return self::$staticMap;
        }

        // Build map by reading MySQL enum columns and matching against auth.php values
        self::$staticMap = self::buildStaticMap();

        return self::$staticMap;
    }

    /**
     * Translate a DB enum value to the current app locale.
     * Uses __() which automatically returns correct locale value.
     *
     * Handles ALL column types — enum, varchar, text, etc.
     * Works for any value that exists in auth.php as a key.
     *
     * @param string $col  DB column name (unused — value lookup is universal)
     * @param string $val  Raw DB value (e.g. 'Penelitian', 'Melakukan Penelitian')
     * @return string      Translated value (e.g. 'Research' when locale=en)
     */
    public static function translateEnum(string $col, string $val): string
    {
        if ($val === '' || $val === null) return $val;

        // Step 1: Try LangSyncService map (enum/set columns)
        $map = self::getEnumTransMap();

        // Direct lookup in the specific column
        if (isset($map[$col][$val])) {
            $langKey = $map[$col][$val];
            $resolved = __($langKey);
            if ($resolved !== $langKey) return $resolved;
        }

        // Normalized lookup in the specific column
        $normVal = preg_replace('/\s+/', '', $val);
        foreach ($map[$col] ?? [] as $dbVal => $langKey) {
            if (preg_replace('/\s+/', '', $dbVal) === $normVal) {
                $resolved = __($langKey);
                if ($resolved !== $langKey) return $resolved;
            }
        }

        // Step 2: Cross-column reverse lookup (free-text columns storing enum-like values)
        foreach ($map as $otherCol => $colMap) {
            foreach ($colMap as $dbVal => $langKey) {
                if (preg_replace('/\s+/', '', $dbVal) === $normVal) {
                    $resolved = __($langKey);
                    if ($resolved !== $langKey) return $resolved;
                }
            }
        }

        // Step 3: Direct __() lookup on auth.php — ANY column value
        // that matches a lang key in auth.php gets translated
        // e.g. text column 'judul_keperluan' = 'Melakukan Penelitian'
        // looks up __('Melakukan Penelitian') if that key exists in auth.php
        $resolved = self::translateRawValue($val);
        if ($resolved !== $val) return $resolved;

        return $val;
    }

    /**
     * Try to translate a raw value by scanning ALL keys in auth.php.
     * Returns original value if no translation found.
     */
    private static function translateRawValue(string $val): string
    {
        static $authId = null;
        static $authEn = null;

        if ($authId === null) {
            $authId = require resource_path('lang/id/auth.php');
            $authEn = require resource_path('lang/en/auth.php');
        }

        // Scan all auth.php keys — if a value matches a lang key, return the translation
        foreach ($authId as $key => $idVal) {
            // Skip if values are the same (not a translatable pair)
            $enVal = $authEn[$key] ?? null;

            // Exact match
            if ($val === $idVal) {
                if ($enVal && $enVal !== $idVal) {
                    // EN translation exists → return it
                    $resolved = __("auth.{$key}");
                    if ($resolved !== "auth.{$key}") return $resolved;
                } else {
                    // EN key missing or same-as-ID → auto-register in both lang files
                    $resolved = AutoLangService::ensureKey($key, $idVal);
                    if ($resolved !== $val) return $resolved;
                }
            }

            // Normalized match (handles whitespace differences)
            if (preg_replace('/\s+/', '', $val) === preg_replace('/\s+/', '', $idVal)) {
                if ($enVal && $enVal !== $idVal) {
                    $resolved = __("auth.{$key}");
                    if ($resolved !== "auth.{$key}") return $resolved;
                } else {
                    $resolved = AutoLangService::ensureKey($key, $idVal);
                    if ($resolved !== $val) return $resolved;
                }
            }
        }

        return $val;
    }

    /**
     * Translate all options for a column.
     *
     * @param string $col      DB column name
     * @param array  $options  Raw option values
     * @return array<string, string>  [raw => translated]
     */
    public static function translateOptions(string $col, array $options): array
    {
        $result = [];
        foreach ($options as $opt) {
            $result[$opt] = self::translateEnum($col, $opt);
        }
        return $result;
    }

    /**
     * Clear cached data and rebuild the map.
     */
    public static function refresh(): array
    {
        self::$staticMap = null;
        return self::getEnumTransMap();
    }

    /**
     * Get all profile column labels from ID lang.
     *
     * @return array<string, string>  [col_name => id_label]
     */
    public static function getColumnLabels(): array
    {
        $labels = [];

        // Known column label keys in auth.php
        $knownLabelKeys = [
            'jenis_kelamin'      => 'auth.col_jenis_kelamin',
            'agama'              => 'auth.col_agama',
            'jenis_keperluan'    => 'auth.col_jenis_keperluan',
            'judul_keperluan'    => 'auth.col_judul_keperluan',
            'nama_instansi'      => 'auth.col_nama_instansi',
            'nip'                => 'auth.col_nip',
            'kartu_identitas'    => 'auth.col_kartu_identitas',
            'alamat'             => 'auth.col_alamat',
            'nomor_whatsapp'     => 'auth.col_nomor_whatsapp',
            'tempat_lahir'      => 'auth.col_tempat_lahir',
            'tanggal_lahir'      => 'auth.col_tanggal_lahir',
            'pangkat_golongan'  => 'auth.col_pangkat_golongan',
            'jabatan'            => 'auth.col_jabatan',
        ];

        foreach ($knownLabelKeys as $colName => $langKey) {
            $resolved = __($langKey);
            if ($resolved !== $langKey) {
                $labels[$colName] = $resolved;
            } else {
                $labels[$colName] = \Illuminate\Support\Str::headline($colName);
            }
        }

        return $labels;
    }

    /**
     * Debug: see what's actually in the map.
     */
    public static function debug(): array
    {
        return [
            'map' => self::getEnumTransMap(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private: build static map from DB + lang files
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the static map by reading MySQL enum/set columns and matching
     * against the auth.php lang values using direct key resolution.
     *
     * Algorithm:
     *  1. Load auth.php ID and EN directly (flat arrays).
     *  2. For each role's profile table, read enum/set columns via SHOW COLUMNS.
     *  3. Match each MySQL enum value to a lang key by checking auth.php values.
     *  4. Build map[col][dbValue] = langKey.
     */
    private static function buildStaticMap(): array
    {
        $map = [];

        // Load auth.php directly — reliable, no glob() needed
        $authId = require resource_path('lang/id/auth.php');
        $authEn = require resource_path('lang/en/auth.php');

        // Build lookup: langValue => langKey for ID (where EN differs = translatable)
        $valToKey = [];
        foreach ($authId as $key => $idVal) {
            if (isset($authEn[$key]) && $authEn[$key] !== $idVal) {
                // This key is translatable
                $fullKey = 'auth.' . $key;
                $valToKey[$idVal] = $fullKey;
                // Also store normalized
                $valToKey[preg_replace('/\s+/', '', $idVal)] = $fullKey;
            }
        }

        // Read MySQL enum columns from each role's profile table
        try {
            $roles = Role::all();
        } catch (\Throwable) {
            return self::fallbackStaticMap($authId, $authEn);
        }

        foreach ($roles as $role) {
            $table = $role->table_name;
            if (!$table) continue;

            try {
                if (!DB::getSchemaBuilder()->hasTable($table)) continue;
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } catch (\Throwable) {
                continue;
            }

            foreach ($columns as $column) {
                $field = $column->Field;
                $type  = $column->Type;

                if (!preg_match('/^(enum|set)\(/i', $type)) continue;

                preg_match_all("/'(.*?)'/", $type, $matches);
                $mysqlVals = $matches[1] ?? [];

                foreach ($mysqlVals as $mysqlVal) {
                    if (isset($valToKey[$mysqlVal])) {
                        $map[$field][$mysqlVal] = $valToKey[$mysqlVal];
                    } else {
                        // Try normalized match
                        $normMysql = preg_replace('/\s+/', '', $mysqlVal);
                        foreach ($valToKey as $lv => $lk) {
                            if (preg_replace('/\s+/', '', $lv) === $normMysql) {
                                $map[$field][$mysqlVal] = $lk;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // If map is still empty, use fallback
        if (empty($map)) {
            return self::fallbackStaticMap($authId, $authEn);
        }

        return $map;
    }

    /**
     * Fallback: manually map known enum columns to auth lang keys.
     * Only activates keys that actually exist in auth.php.
     */
    private static function fallbackStaticMap(array $authId, array $authEn): array
    {
        $map = [];

        $fallback = [
            'jenis_kelamin' => [
                'Laki-Laki'      => 'auth.male',
                'Laki - Laki'    => 'auth.male',
                'Perempuan'      => 'auth.female',
            ],
            'jenis_keperluan' => [
                'Penelitian'         => 'auth.purpose_research',
                'Kunjungan'          => 'auth.purpose_visit',
                'Hanya Daftar Akun'  => 'auth.purpose_register_only',
            ],
            'agama' => [
                'Islam'     => 'auth.religion_islam',
                'Kristen'   => 'auth.religion_christian',
                'Katolik'   => 'auth.religion_catholic',
                'Hindu'     => 'auth.religion_hindu',
                'Buddha'    => 'auth.religion_buddha',
                'Konghucu'  => 'auth.religion_confucian',
            ],
        ];

        foreach ($fallback as $col => $mapping) {
            foreach ($mapping as $dbVal => $langKey) {
                // Strip 'auth.' prefix since auth.php keys are unprefixed
                $plainKey = str_starts_with($langKey, 'auth.') ? substr($langKey, 5) : $langKey;
                $idVal = $authId[$plainKey] ?? null;
                $enVal = $authEn[$plainKey] ?? null;
                if ($idVal && $enVal && $idVal !== $enVal) {
                    $map[$col][$dbVal] = $langKey;
                }
            }
        }

        return $map;
    }
}