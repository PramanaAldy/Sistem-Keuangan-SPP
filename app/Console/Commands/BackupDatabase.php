<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup {--keep=30 : Jumlah file backup yang disimpan}';
    protected $description = 'Backup database MySQL ke folder storage/app/backups';

    public function handle(): int
    {
        $keep     = (int) $this->option('keep');
        $dbName   = config('database.connections.mysql.database');
        $dbHost   = config('database.connections.mysql.host');
        $dbPort   = config('database.connections.mysql.port');
        $dbUser   = config('database.connections.mysql.username');
        $dbPass   = config('database.connections.mysql.password');

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = "backup_{$dbName}_" . now()->format('Y-m-d_H-i-s') . ".sql";
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Buat perintah mysqldump
        $passOption = $dbPass ? "-p\"{$dbPass}\"" : '';
        $command = "mysqldump --host={$dbHost} --port={$dbPort} --user={$dbUser} {$passOption} {$dbName} > \"{$filepath}\" 2>&1";

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($filepath) || filesize($filepath) < 100) {
            $this->error("❌ Backup GAGAL! Exit code: {$exitCode}");
            if (file_exists($filepath)) unlink($filepath);
            return self::FAILURE;
        }

        $size = round(filesize($filepath) / 1024, 1);
        $this->info("✅ Backup berhasil: {$filename} ({$size} KB)");

        // Hapus file backup lama — hanya simpan N terbaru
        $files = glob($backupDir . DIRECTORY_SEPARATOR . "backup_{$dbName}_*.sql");
        if ($files && count($files) > $keep) {
            usort($files, fn($a, $b) => filemtime($a) - filemtime($b)); // oldest first
            $toDelete = array_slice($files, 0, count($files) - $keep);
            foreach ($toDelete as $old) {
                unlink($old);
                $this->line("🗑  Dihapus: " . basename($old));
            }
        }

        return self::SUCCESS;
    }
}
