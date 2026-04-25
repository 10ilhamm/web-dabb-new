<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class AutoNpmBuild extends Command
{
    protected $signature = 'npm:build {--force : Paksa build tanpa mengecek perubahan file}';
    protected $description = 'Auto build assets (CSS/JS) jika ada perubahan pada file resources';

    private string $markerFile;
    private string $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->markerFile = storage_path('app/.npm_build_last_run');
        $this->logFile = storage_path('logs/npm-build.log');
    }

    public function handle()
    {
        $projectPath = base_path();
        $markerTime = $this->getMarkerTime();
        $now = time();

        $this->log("=== npm:build check at " . date('Y-m-d H:i:s', $now) . " ===");
        $this->log("Marker file: {$this->markerFile}");
        $this->log("Marker time: " . date('Y-m-d H:i:s', $markerTime) . " (timestamp: {$markerTime})");

        // Jika marker belum ada (pertama kali), tulis marker sekarang dan skip build
        // Ini mencegah build berulang-ulang saat pertama kali dijalankan
        if ($markerTime === 0) {
            $this->log("First run detected. Writing initial marker without building.");
            $this->writeMarker($projectPath);
            $this->info('Marker inisialisasi ditulis. Build berikutnya akan mengecek perubahan file.');
            $this->log("=== end check (first run) ===\n");
            return 0;
        }

        $hasChanges = $this->hasChanges($projectPath);

        $this->log("Force option: " . ($this->option('force') ? 'YES' : 'NO'));
        $this->log("Has changes: " . ($hasChanges ? 'YES' : 'NO'));

        if ($this->option('force') || $hasChanges) {
            $this->log("DECISION: RUNNING npm run build");

            $this->info('Perubahan terdeteksi atau mode force. Menjalankan npm run build...');

            $cmd = $this->resolveNpmCommand($projectPath);
            $this->log("Executing: {$cmd}");

            $result = Process::run($cmd);

            if ($result->successful()) {
                $this->writeMarker($projectPath);
                $this->info('npm run build berhasil dijalankan.');
                $this->log("SUCCESS: Build completed, marker updated to " . date('Y-m-d H:i:s', time()));
            } else {
                $this->error('npm run build gagal:');
                $this->error($result->errorOutput());
                $this->log("FAILED: " . $result->errorOutput());
            }
        } else {
            $this->info('Tidak ada perubahan pada file CSS/JS/Blade. Skip build.');
            $this->log("DECISION: SKIPPED (no changes)");
        }

        $this->log("=== end check ===\n");
        return 0;
    }

    /**
     * Resolve npm command: coba sail jika Docker running, fallback ke npm langsung.
     */
    private function resolveNpmCommand(string $projectPath): string
    {
        // Cek apakah Docker running
        $dockerCheck = Process::run('docker info');
        $dockerRunning = $dockerCheck->successful();

        $this->log("Docker running: " . ($dockerRunning ? 'YES' : 'NO'));

        if ($dockerRunning) {
            $sailPath = base_path('vendor/bin/sail');
            if (file_exists($sailPath)) {
                $this->log("Using sail for npm build.");
                return "cd {$projectPath} && {$sailPath} npm run build";
            }
        }

        $this->log("Using local npm directly.");
        return "cd {$projectPath} && npm run build";
    }

    /**
     * Cek apakah ada file yang berubah sejak build terakhir.
     */
    private function hasChanges(string $projectPath): bool
    {
        $markerTime = $this->getMarkerTime();

        $watchPaths = [
            "{$projectPath}/resources/css",
            "{$projectPath}/resources/js",
            "{$projectPath}/resources/views",
            "{$projectPath}/tailwind.config.js",
            "{$projectPath}/vite.config.js",
            "{$projectPath}/package.json",
        ];

        foreach ($watchPaths as $path) {
            if (!file_exists($path)) {
                $this->log("Path not found: {$path}");
                continue;
            }

            if (is_dir($path)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    $fileMTime = $file->getMTime();
                    if ($fileMTime > $markerTime) {
                        $this->log("CHANGE DETECTED: {$file->getPathname()} (mtime: " . date('Y-m-d H:i:s', $fileMTime) . " > marker: " . date('Y-m-d H:i:s', $markerTime) . ")");
                        return true;
                    }
                }
            } else {
                $fileMTime = filemtime($path);
                if ($fileMTime > $markerTime) {
                    $this->log("CHANGE DETECTED: {$path} (mtime: " . date('Y-m-d H:i:s', $fileMTime) . " > marker: " . date('Y-m-d H:i:s', $markerTime) . ")");
                    return true;
                }
            }
        }

        $this->log("No changes detected in any watched path.");
        return false;
    }

    private function getMarkerTime(): int
    {
        if (!file_exists($this->markerFile)) {
            $this->log("Marker file not found, returning 0");
            return 0;
        }

        $content = file_get_contents($this->markerFile);
        $time = is_numeric($content) ? (int) $content : 0;
        $this->log("Marker file exists, time: {$time}");
        return $time;
    }

    private function writeMarker(string $projectPath): void
    {
        file_put_contents($this->markerFile, time());
        $this->log("Marker written: " . date('Y-m-d H:i:s', time()));
    }

    private function log(string $message): void
    {
        $line = "[" . date('Y-m-d H:i:s') . "] {$message}" . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
