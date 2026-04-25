<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoClearLogs extends Command
{
    protected $signature = 'log:clear';
    protected $description = 'Hapus semua file log di storage/logs/ untuk meringankan sistem';

    public function handle()
    {
        $logDir = storage_path('logs');
        $deleted = 0;

        if (is_dir($logDir)) {
            foreach (new \DirectoryIterator($logDir) as $file) {
                if ($file->isDot() || $file->getFilename() === '.gitignore') {
                    continue;
                }
                if ($file->isFile() && @unlink($file->getPathname())) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("{$deleted} file log berhasil dihapus.");
        } else {
            $this->info('Tidak ada file log yang perlu dihapus.');
        }

        return 0;
    }
}

