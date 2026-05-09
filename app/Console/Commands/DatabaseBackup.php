<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Create a local database backup (SQL dump) and save it to storage/backups/';

    public function handle(): int
    {
        $config   = config('database.connections.' . config('database.default'));
        $driver   = $config['driver'];
        $database = $config['database'];
        $host     = $config['host']     ?? '127.0.0.1';
        $port     = $config['port']     ?? 5432;
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        $filename  = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if ($driver === 'pgsql') {
            // PostgreSQL
            putenv("PGPASSWORD={$password}");
            $command = sprintf(
                'pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-password --format=plain --file=%s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
        } elseif ($driver === 'mysql') {
            // MySQL / MariaDB
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
        } else {
            $this->error("Unsupported database driver: {$driver}");
            return self::FAILURE;
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error("Backup failed! Return code: {$returnCode}");
            return self::FAILURE;
        }

        // Keep only the last 30 backups to save disk space
        $this->pruneOldBackups($backupDir, 30);

        $this->info("✅ Backup saved: storage/backups/{$filename}");
        return self::SUCCESS;
    }

    /**
     * Delete oldest backup files if more than $keep files exist.
     */
    private function pruneOldBackups(string $dir, int $keep): void
    {
        $files = glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        if (!$files || count($files) <= $keep) return;

        usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
        $toDelete = array_slice($files, 0, count($files) - $keep);
        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }
}
