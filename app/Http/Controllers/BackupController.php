<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('backups');
    }

    /**
     * List all backup files.
     */
    public function index()
    {
        $backups = [];

        if (is_dir($this->backupDir)) {
            $files = glob($this->backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql') ?: [];
            rsort($files); // newest first

            foreach ($files as $file) {
                $backups[] = [
                    'name'     => basename($file),
                    'size'     => $this->formatBytes(filesize($file)),
                    'created'  => date('m/d/Y h:i A', filemtime($file)),
                    'path'     => $file,
                ];
            }
        }

        return view('backups.index', compact('backups'));
    }

    /**
     * Run a manual backup now.
     */
    public function run()
    {
        $exitCode = Artisan::call('db:backup');

        if ($exitCode !== 0) {
            return redirect()->route('backups.index')->with('error', 'Backup failed. The database dump tool (pg_dump) may not be available on this server.');
        }

        return redirect()->route('backups.index')->with('success', 'Backup created successfully.');
    }

    /**
     * Restore the database from an uploaded .sql file.
     * Uses PHP/PDO directly — fast, no psql subprocess needed.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'mimes:sql,txt', 'max:51200'],
        ]);

        $tmpName = 'restore_tmp_' . time() . '.sql';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $request->file('sql_file')->move($this->backupDir, $tmpName);
        $tmpPath = $this->backupDir . DIRECTORY_SEPARATOR . $tmpName;

        try {
            $sql = file_get_contents($tmpPath);

            if (empty(trim($sql))) {
                throw new \Exception('The uploaded file is empty.');
            }

            // Execute the SQL dump directly via Laravel's DB connection (fast — no subprocess)
            \Illuminate\Support\Facades\DB::unprepared($sql);

        } catch (\Exception $e) {
            @unlink($tmpPath);
            return redirect()->route('backups.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }

        @unlink($tmpPath);

        return redirect()->route('backups.index')
            ->with('success', '✅ Database restored successfully from uploaded backup.');
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $filename): BinaryFileResponse
    {
        // Sanitize filename — only allow our backup naming pattern
        if (!preg_match('/^backup_[\d_-]+\.sql$/', $filename)) {
            abort(404);
        }

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($path);
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy(string $filename)
    {
        if (!preg_match('/^backup_[\d_-]+\.sql$/', $filename)) {
            abort(404);
        }

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->route('backups.index')->with('success', 'Backup deleted.');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
