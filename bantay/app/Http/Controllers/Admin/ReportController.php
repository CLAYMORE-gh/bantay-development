<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\Disease;
use App\Models\ReportingPeriod;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $types = ReportService::TYPES;
        return view('admin.reports.index', compact('types'));
    }

    public function create(string $type)
    {
        if (!array_key_exists($type, ReportService::TYPES)) {
            abort(404);
        }

        $meta = ReportService::TYPES[$type];
        $diseases = Disease::active()->orderBy('name')->get();
        $barangays = Barangay::active()->orderBy('name')->get();
        $periods = ReportingPeriod::active()->orderByDesc('start_date')->get();

        return view('admin.reports.create', compact('type', 'meta', 'diseases', 'barangays', 'periods'));
    }

    public function show(Request $request, string $type, ReportService $service)
    {
        $params = $this->buildParams($request);
        $report = $service->generate($type, $params);

        $this->logReport($request, $type, 'report_viewed', $params, $report);

        return view('admin.reports.show', compact('report', 'type', 'params'));
    }

    public function exportPdf(Request $request, string $type, ReportService $service)
    {
        $params = $this->buildParams($request);
        $report = $service->generate($type, $params);

        $this->logReport($request, $type, 'report_exported_pdf', $params, $report);

        $pdf = Pdf::loadView('exports.report-pdf', [
            'report' => $report,
            'generatedBy' => $request->user()->name,
        ]);

        $filename = str($report['title'])->slug() . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request, string $type, ReportService $service)
    {
        $params = $this->buildParams($request);
        $report = $service->generate($type, $params);

        $this->logReport($request, $type, 'report_exported_excel', $params, $report);

        $filename = str($report['title'])->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ReportExport($report), $filename);
    }

    private function buildParams(Request $request): array
    {
        return array_filter([
            'period_id' => $request->integer('period_id') ?: null,
            'disease_id' => $request->integer('disease_id') ?: null,
            'barangay_id' => $request->integer('barangay_id') ?: null,
        ]);
    }

    private function logReport(Request $request, string $type, string $action, array $params, array $report): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'after' => [
                'report_type' => $type,
                'title' => $report['title'],
                'subtitle' => $report['subtitle'],
                'filters' => $params,
            ],
            'ip_address' => $request->ip(),
        ]);
    }
}