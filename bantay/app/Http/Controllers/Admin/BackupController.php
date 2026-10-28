<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private function pgDumpPath(): string
    {
        return config('bantay.pg_dump_path', 'pg_dump');
    }

    private function pgRestorePath(): string
    {
        return config('bantay.pg_restore_path', 'pg_restore');
    }

    public function index()
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files('backups'))
            ->filter(fn ($f) => str_ends_with($f, '.dump'))
            ->map(fn ($f) => [
                'path' => $f,
                'name' => basename($f),
                'size_mb' => round($disk->size($f) / 1048576, 2),
                'created_at' => \Carbon\Carbon::createFromTimestamp($disk->lastModified($f)),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('admin.backup.index', compact('files'));
    }

    public function create(Request $request)
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        $filename = 'backups/bantay-' . now()->format('Y-m-d-His') . '.dump';
        $fullPath = storage_path('app/' . $filename);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $process = new Process([
            $this->pgDumpPath(),
            '-h', $dbConfig['host'],
            '-p', (string) $dbConfig['port'],
            '-U', $dbConfig['username'],
            '-d', $dbConfig['database'],
            '-F', 'c',
            '-f', $fullPath,
        ]);
        $process->setEnv(['PGPASSWORD' => $dbConfig['password']]);
        $process->setTimeout(300);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            return back()->withErrors(['backup' => 'Backup failed: ' . $e->getMessage()]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'backup_created',
            'after' => ['filename' => $filename],
        ]);

        return redirect()->route('admin.backup.index')->with('status', 'Backup created successfully.');
    }

    public function download(string $filename)
    {
        $path = 'backups/' . basename($filename);

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }

    public function destroy(string $filename)
    {
        $path = 'backups/' . basename($filename);
        Storage::disk('local')->delete($path);

        return redirect()->route('admin.backup.index')->with('status', 'Backup file deleted.');
    }

    public function showRestore(string $filename)
    {
        $path = 'backups/' . basename($filename);

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return view('admin.backup.restore', ['filename' => basename($filename)]);
    }

    /**
     * Destructive — requires the Administrator to type the exact filename
     * as a confirmation step, matching the multi-step confirm flow in the
     * wireframe.
     */
    public function restore(Request $request, string $filename)
    {
        $validated = $request->validate([
            'confirm_filename' => 'required|string',
        ]);

        if ($validated['confirm_filename'] !== $filename) {
            return back()->withErrors(['confirm_filename' => 'The typed filename does not match. Restore cancelled.']);
        }

        $path = storage_path('app/backups/' . basename($filename));

        if (!file_exists($path)) {
            abort(404);
        }

        $dbConfig = config('database.connections.' . config('database.default'));

        $process = new Process([
            $this->pgRestorePath(),
            '-h', $dbConfig['host'],
            '-p', (string) $dbConfig['port'],
            '-U', $dbConfig['username'],
            '-d', $dbConfig['database'],
            '--clean', '--if-exists',
            $path,
        ]);
        $process->setEnv(['PGPASSWORD' => $dbConfig['password']]);
        $process->setTimeout(600);

        try {
            $process->run();
        } catch (\Throwable $e) {
            return back()->withErrors(['restore' => 'Restore failed: ' . $e->getMessage()]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'backup_restored',
            'after' => ['filename' => $filename],
        ]);

        return redirect()->route('admin.backup.index')
            ->with('status', "Database restored from {$filename}. Verify application functionality before resuming normal operations.");
    }
}
