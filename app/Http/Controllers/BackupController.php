<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;
use ZipArchive;
use RuntimeException;

class BackupController extends Controller
{
    private const BACKUP_DIR = 'backups';

    /**
     * Display a list of all backups.
     */
    public function index()
    {
        Gate::authorize('admin-only');

        $backupPath = storage_path('app/' . self::BACKUP_DIR);
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $files = FileFacade::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        // Sort backups: latest first
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return view('backups.index', compact('backups'));
    }

    /**
     * Create a new system backup (SQLite DB + Encrypted Files).
     */
    public function create(Request $request)
    {
        Gate::authorize('admin-only');

        $backupPath = storage_path('app/' . self::BACKUP_DIR);
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $zipName = 'backup_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.zip';
        $zipFullPath = $backupPath . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['error' => 'Não foi possível criar o ficheiro ZIP de backup.']);
        }

        // 1. Add SQLite Database if SQLite is in use
        $dbConnection = config('database.default');
        $dbAdded = false;
        
        if ($dbConnection === 'sqlite') {
            $sqlitePath = config('database.connections.sqlite.database');
            if (file_exists($sqlitePath)) {
                $zip->addFile($sqlitePath, 'database/database.sqlite');
                $dbAdded = true;
            }
        }

        // Fallback: if database is not sqlite or database.sqlite is missing, we dump database rows to JSON
        if (!$dbAdded) {
            $dbDump = $this->dumpDatabaseToJson();
            $zip->addFromString('database/database_dump.json', $dbDump);
        }

        // 2. Add Encrypted Files
        $encryptedFilesDir = storage_path('app/encrypted_files');
        if (file_exists($encryptedFilesDir)) {
            $files = FileFacade::allFiles($encryptedFilesDir);
            foreach ($files as $file) {
                $zip->addFile($file->getRealPath(), 'encrypted_files/' . $file->getFilename());
            }
        }

        $zip->close();

        // Audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_created',
            'entity_type' => AuditLog::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'backup_name' => $zipName,
                'size' => filesize($zipFullPath),
                'message' => 'Cópia de segurança criada com sucesso.'
            ]
        ]);

        return redirect()->route('backups.index')->with('status', 'Backup criado com sucesso: ' . $zipName);
    }

    /**
     * Download a backup file.
     */
    public function download(Request $request, string $filename)
    {
        Gate::authorize('admin-only');

        // Prevent directory traversal
        $filename = basename($filename);
        $filePath = storage_path('app/' . self::BACKUP_DIR . '/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'O ficheiro de backup não existe.');
        }

        // Audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_downloaded',
            'entity_type' => AuditLog::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['backup_name' => $filename]
        ]);

        return response()->download($filePath);
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request, string $filename)
    {
        Gate::authorize('admin-only');

        // Prevent directory traversal
        $filename = basename($filename);
        $filePath = storage_path('app/' . self::BACKUP_DIR . '/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'O ficheiro de backup não existe.');
        }

        unlink($filePath);

        // Audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_deleted',
            'entity_type' => AuditLog::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['backup_name' => $filename]
        ]);

        return redirect()->route('backups.index')->with('status', 'Cópia de segurança eliminada.');
    }

    /**
     * Serializes database records to a JSON format (fallback database dump).
     */
    private function dumpDatabaseToJson(): string
    {
        $tables = ['departments', 'users', 'files', 'file_shares', 'audit_logs', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'];
        $dump = [];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $dump[$table] = \Illuminate\Support\Facades\DB::table($table)->get()->toArray();
            }
        }

        return json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
