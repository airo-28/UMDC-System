<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseBackup extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Create a SQL data backup using PHP/PDO (fast, no pg_dump needed)';

    public function handle(): int
    {
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $sql = $this->generateSqlDump();
            file_put_contents($filepath, $sql);
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Keep only the last 30 backups
        $this->pruneOldBackups($backupDir, 30);

        $this->info("✅ Backup saved: storage/backups/{$filename}");
        return self::SUCCESS;
    }

    /**
     * Generate a SQL dump using PHP/PDO — reuses the existing DB connection.
     * Dumps all table data as INSERT statements with TRUNCATE headers.
     */
    private function generateSqlDump(): string
    {
        $driver = config('database.default');
        $lines  = [];

        $lines[] = '-- UM Dining Center Database Backup';
        $lines[] = '-- Generated: ' . now()->format('Y-m-d H:i:s T');
        $lines[] = '-- Driver: ' . $driver;
        $lines[] = '';

        // Get table names
        $tables = $this->getTableNames($driver);

        if (empty($tables)) {
            throw new \Exception('No tables found in the database.');
        }

        $pdo = DB::connection()->getPdo();

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();

            $lines[] = "-- -----------------------------------------------";
            $lines[] = "-- Table: {$table}";
            $lines[] = "-- -----------------------------------------------";

            if ($rows->isEmpty()) {
                $lines[] = "-- (no rows)";
                $lines[] = '';
                continue;
            }

            // TRUNCATE before re-inserting so restore is clean
            if ($driver === 'pgsql') {
                $lines[] = "TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE;";
            } else {
                $lines[] = "SET FOREIGN_KEY_CHECKS=0;";
                $lines[] = "TRUNCATE TABLE `{$table}`;";
                $lines[] = "SET FOREIGN_KEY_CHECKS=1;";
            }

            foreach ($rows as $row) {
                $rowArr  = (array) $row;
                $columns = $this->quoteColumns(array_keys($rowArr), $driver);
                $values  = $this->quoteValues(array_values($rowArr), $pdo);

                $tbl = $driver === 'pgsql' ? "\"{$table}\"" : "`{$table}`";
                $lines[] = "INSERT INTO {$tbl} ({$columns}) VALUES ({$values});";
            }

            $lines[] = '';
        }

        return implode("\n", $lines) . "\n";
    }

    private function getTableNames(string $driver): array
    {
        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            return array_map(fn($r) => $r->tablename, $rows);
        }

        // MySQL / MariaDB
        $rows = DB::select('SHOW TABLES');
        return array_map(fn($r) => array_values((array)$r)[0], $rows);
    }

    private function quoteColumns(array $cols, string $driver): string
    {
        $q = $driver === 'pgsql' ? '"' : '`';
        return implode(', ', array_map(fn($c) => $q . $c . $q, $cols));
    }

    private function quoteValues(array $values, \PDO $pdo): string
    {
        return implode(', ', array_map(function ($val) use ($pdo) {
            if ($val === null)         return 'NULL';
            if ($val === true)         return 'TRUE';
            if ($val === false)        return 'FALSE';
            if (is_int($val) || is_float($val)) return $val;
            return $pdo->quote((string) $val);
        }, $values));
    }

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
