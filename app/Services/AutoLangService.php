<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * AutoLangService — automatically registers missing translation keys
 * in lang/id/auth.php and lang/en/auth.php.
 *
 * Called by LangSyncService whenever a DB value has no existing lang key.
 * Uses heuristics (dictionary + patterns) for known values,
 * and falls back to TranslationService (Google Translate API) for unknown text.
 */
class AutoLangService
{
    private static ?array $idLang = null;
    private static ?array $enLang = null;

    /**
     * Ensure a translation key exists in both id/auth.php and en/auth.php.
     * If the key is missing in ID → add with the original ID value.
     * If the key is missing in EN → generate translation or use placeholder.
     *
     * @param string $key       Full lang key without prefix (e.g. 'melakukan_penelitian')
     * @param string $idValue   The Indonesian source value (e.g. 'Melakukan Penelitian')
     * @return string           The EN translation (or generated value)
     */
    public static function ensureKey(string $key, string $idValue): string
    {
        self::loadLangFiles();

        $idKey = "auth.{$key}";
        $idMissing = !isset(self::$idLang[$key]);
        $enMissing = !isset(self::$enLang[$key]);

        if (!$idMissing && !$enMissing) {
            // Both exist — return current EN value
            return self::$enLang[$key];
        }

        // Generate EN translation from ID value
        $enValue = self::generateEnTranslation($key, $idValue);

        if ($idMissing) {
            self::addKey('id/auth.php', $key, $idValue);
            self::$idLang[$key] = $idValue;
        }

        if ($enMissing) {
            self::addKey('en/auth.php', $key, $enValue);
            self::$enLang[$key] = $enValue;
        }

        return $enValue;
    }

    /**
     * Load both lang files into memory (cached).
     */
    private static function loadLangFiles(): void
    {
        if (self::$idLang === null) {
            $idPath = resource_path('lang/id/auth.php');
            $enPath = resource_path('lang/en/auth.php');
            self::$idLang = file_exists($idPath) ? require $idPath : [];
            self::$enLang = file_exists($enPath) ? require $enPath : [];
        }
    }

    /**
     * Generate an English translation from Indonesian text using heuristics.
     * Falls back to a clear placeholder if no rule matches.
     */
    private static function generateEnTranslation(string $key, string $idValue): string
    {
        // ── Known translation dictionary ──────────────────────────────────────
        $dict = [
            // Common judul_keperluan / free-text values
            'Melakukan Penelitian'        => 'Conducting Research',
            'Penelitian Sejarah'          => 'Historical Research',
            'Penelitian Pasar'           => 'Market Research',
            'Penelitian'                  => 'Research',
            'Kunjungan'                   => 'Visit',
            'Hanya Daftar Akun'          => 'Register Only',

            // Address terms
            'Kabupaten'                  => 'Regency of',
            'Kota'                        => 'City of',
            'Provinsi'                    => 'Province of',
            'Kecamatan'                   => 'District of',
            'Kelurahan'                   => 'Village of',
            'Desa'                        => 'Village of',

            // Gender
            'Laki-Laki'                   => 'Male',
            'Laki - Laki'                 => 'Male',
            'Perempuan'                   => 'Female',

            // Religion
            'Islam'                       => 'Islam',
            'Kristen'                     => 'Christian',
            'Katolik'                     => 'Catholic',
            'Hindu'                       => 'Hindu',
            'Buddha'                      => 'Buddha',
            'Konghucu'                    => 'Confucian',

            // Roles / account types
            'Umum'                        => 'General',
            'Pelajar / Mahasiswa'        => 'Student',
            'Instansi / Swasta'          => 'Institution / Private',
            'Administrator'               => 'Administrator',
            'Pegawai'                     => 'Employee',

            // Identity card types
            'Kartu Identitas (KTP)'      => 'Identity Card (KTP)',
            'Kartu Identitas (KTM/Pelajar)' => 'Identity Card (Student ID)',
            'Kartu Identitas Instansi'   => 'Institution Identity Card',

            // Common purposes
            'Pendaftaran Akun Pengguna'  => 'User Account Registration',
            'Melihat Koleksi'            => 'Viewing Collection',

            // Common virtual room / book / building names
            'Ruang Utama'                => 'Main Room',
            'Ruang Referensi'            => 'Reference Room',
            'Ruang Koleksi'              => 'Collection Room',
            'Ruang Pameran'              => 'Exhibition Room',
            'Ruang Arsip'                => 'Archive Room',
            'Ruang Administrasi'         => 'Administration Room',
            'Ruang Server'               => 'Server Room',
            'Ruang Meeting'              => 'Meeting Room',
            'Ruang baca'                 => 'Reading Room',
            'RuangBaca'                  => 'Reading Room',
            'Aula Utama'                  => 'Main Hall',
            'Aula Serba Guna'            => 'Multi-Purpose Hall',
            'Gedung A'                    => 'Building A',
            'Gedung B'                    => 'Building B',
            'Gudang Arsip'               => 'Archive Warehouse',
            'Gudang Lama'                => 'Old Warehouse',
            'Lobby'                       => 'Lobby',
            'Teras'                       => 'Terrace',
            'Taman'                       => 'Garden',
            'Ruang Digital'              => 'Digital Room',
            'Ruang Multimedia'           => 'Multimedia Room',
            'Ruang Restorasi'            => 'Restoration Room',
            'Ruang Penyimpanan'          => 'Storage Room',
            'Permintaan Data'            => 'Data Request',
        ];

        // ── Pattern-based translation ─────────────────────────────────────────
        // Try exact match in dictionary first
        if (isset($dict[$idValue])) {
            return $dict[$idValue];
        }

        // ── Pattern-based translation (fallback only — Google Translate is primary for multi-word) ──
        // For single-word matches like "Ruang Utama" or "Ruang Meeting" → pattern is better
        // For multi-word names (2+ words) → let Google Translate handle it
        $wordCount = preg_match_all('/\S+/', $idValue, $w);
        $isSingleWord = $wordCount <= 1;

        if ($isSingleWord) {
            // Pattern: "Ruang X" → "X Room"
            if (preg_match('/^Ruang\s+(.+)/u', $idValue, $m)) {
                return ucfirst($m[1]) . ' Room';
            }

            // Pattern: "Aula X" → "X Hall"
            if (preg_match('/^Aula\s+(.+)/u', $idValue, $m)) {
                return ucfirst($m[1]) . ' Hall';
            }

            // Pattern: "Gedung X" → "Building X"
            if (preg_match('/^Gedung\s+(.+)/u', $idValue, $m)) {
                return 'Building ' . ucfirst($m[1]);
            }

            // Pattern: "Gudang X" → "X Warehouse"
            if (preg_match('/^Gudang\s+(.+)/u', $idValue, $m)) {
                return ucfirst($m[1]) . ' Warehouse';
            }

            // Pattern: "Ruangan X" → "Room X"
            if (preg_match('/^Ruangan\s+(.+)/u', $idValue, $m)) {
                return 'Room ' . ucfirst($m[1]);
            }
        }

        // Pattern: "Melakukan X" → "Conducting X"
        if (preg_match('/^Melakukan\s+(.+)/u', $idValue, $m)) {
            $enAction = self::actionToEn($m[1]);
            return "Conducting {$enAction}";
        }

        // Pattern: capitalized compound words (pascal/camel case ID) — try word-by-word
        // e.g. "KoleksiDigital" → try "Koleksi Digital" in dict
        $withSpace = preg_replace('/(?<=[a-z])(?=[A-Z])/u', ' ', $idValue);
        if ($withSpace !== $idValue && isset($dict[$withSpace])) {
            return $dict[$withSpace];
        }

        // ── Fallback: use Google Translate API (TranslationService) ───────────
        try {
            $ts = app(TranslationService::class);
            $en = $ts->translate($idValue);
            // Only use if Google actually translated it (different from original)
            if ($en !== $idValue && strpos($en, 'TODO') === false) {
                return $en;
            }
        } catch (\Throwable $e) {
            // Google Translate failed — silently skip
        }

        // ── Ultimate fallback: clear TODO marker so translators know what to fix
        // The EN value gets a TODO prefix; English users see it as a clear signal
        // that this key needs manual review in lang files.
        $sanitized = trim(preg_replace('/[^a-zA-Z0-9\s\-\/]/u', '', $idValue));
        return 'TODO: ' . $sanitized;
    }

    /**
     * Convert common Indonesian action verbs to English gerund forms.
     */
    private static function actionToEn(string $phrase): string
    {
        $map = [
            'penelitian'   => 'Research',
            'pasar'         => 'Market Research',
            'sejarah'       => 'Historical Research',
            'kunjungan'     => 'Visits',
            'observasi'     => 'Observation',
            'pendataan'     => 'Data Collection',
            'pendaftaran'   => 'Registration',
            'verifikasi'    => 'Verification',
            'peminjaman'    => 'Borrowing',
            'pengembalian'  => 'Return',
            'Permintaan'    => 'Request',
        ];

        foreach ($map as $id => $en) {
            if (str_starts_with(strtolower($phrase), strtolower($id))) {
                return str_replace($id, $en, $phrase);
            }
        }

        return ucfirst(strtolower($phrase));
    }

    /**
     * Append a new key=>value entry to a lang PHP file.
     * Detects comment groups and inserts near the relevant section.
     *
     * @param string $filePath  Relative path from base_path() (e.g. 'id/auth.php')
     * @param string $key       Array key (without quotes prefix)
     * @param string $value     Translated value
     */
    public static function addKey(string $filePath, string $key, string $value): bool
    {
        $fullPath = resource_path("lang/{$filePath}");

        if (!file_exists($fullPath)) {
            return false;
        }

        $content = file_get_contents($fullPath);

        // Guard: don't add if key already exists (even unquoted)
        if (preg_match("/'{$key}'/", $content)) {
            return false;
        }

        // Determine where to insert — prefer "Common" comment group if it exists
        $insertMarker = "    // Column labels (dynamic fields)";
        $insertBefore = false;

        if (strpos($content, '// Common') !== false) {
            // Insert right before "// Common..." comment
            $insertMarker = "    // Common";
            $insertBefore = true;
        }

        $pos = strpos($content, $insertMarker);

        if ($pos === false) {
            // Fallback: insert before "// Column labels"
            $pos = strpos($content, "    // Column labels");
        }

        if ($pos === false) {
            // Fallback: insert before the closing ];
            $pos = strrpos($content, "];");
        }

        if ($pos === false) {
            return false;
        }

        // Escape single quotes in value
        $escapedValue = str_replace("'", "\\'", $value);
        $newEntry = "    '{$key}' => '{$escapedValue}'," . PHP_EOL;

        if ($insertBefore) {
            $newContent = substr($content, 0, $pos) . $newEntry . substr($content, $pos);
        } else {
            $newContent = substr($content, 0, $pos) . $newEntry . substr($content, $pos);
        }

        return file_put_contents($fullPath, $newContent) !== false;
    }

    /**
     * Clear the in-memory cache (useful for testing).
     */
    public static function clearCache(): void
    {
        self::$idLang = null;
        self::$enLang = null;
    }
}
