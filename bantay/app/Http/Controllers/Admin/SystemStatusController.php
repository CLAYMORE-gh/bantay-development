<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\DatasetUpload;
use App\Models\SurveillanceRecord;
use Illuminate\Support\Facades\DB;

class SystemStatusController extends Controller
{
    public function index()
    {
        $dbStatus = $this->checkDatabase();
        $queueStatus = $this->checkQueue();

        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_records' => SurveillanceRecord::count(),
            'total_uploads' => DatasetUpload::count(),
            'open_alerts' => Alert::openStatus()->count(),
            'last_upload' => DatasetUpload::latest('created_at')->first(),
        ];

        $environment = [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'On (should be Off in production)' : 'Off',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_driver' => config('database.default'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
        ];

        $storage = $this->checkStorage();

        return view('admin.system-status.index', compact('dbStatus', 'queueStatus', 'stats', 'environment', 'storage'));
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('select 1');
            $ms = round((microtime(true) - $start) * 1000, 1);
            return ['ok' => true, 'message' => "Connected ({$ms}ms)"];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            return ['ok' => true, 'message' => 'Sync driver — jobs run immediately, no worker needed (fine for dev/demo)'];
        }

        // For database/redis drivers, a pending-job count is the best simple signal
        // available without a dedicated monitoring package.
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            return ['ok' => $failed === 0, 'message' => "{$pending} pending, {$failed} failed — make sure 'php artisan queue:work' is running"];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not check queue tables: ' . $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || $total === false) {
            return ['ok' => null, 'message' => 'Disk usage unavailable on this system.'];
        }

        $usedPercent = round((($total - $free) / $total) * 100, 1);

        return [
            'ok' => $usedPercent < 90,
            'free_gb' => round($free / 1073741824, 1),
            'total_gb' => round($total / 1073741824, 1),
            'used_percent' => $usedPercent,
        ];
    }
}
