<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDatasetUpload;
use App\Models\DatasetUpload;
use App\Models\ReportingPeriod;
use App\Models\SurveillanceRecord;
use App\Services\DatasetValidationService;
use App\Models\ValidationFinding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DatasetUploadController extends Controller
{
    public function index()
    {
        $uploads = DatasetUpload::with('reportingPeriod', 'uploader')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.uploads.index', compact('uploads'));
    }

    public function create()
    {
        $periods = ReportingPeriod::active()->orderByDesc('start_date')->get();
        return view('admin.uploads.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reporting_period_id' => 'required|exists:reporting_periods,id',
            'file' => 'required|file|mimes:xlsx,xls|max:20480', // 20MB
        ]);

        $file = $request->file('file');
        $fileHash = hash_file('sha256', $file->getRealPath());

        // File-level duplicate detection
        $existing = DatasetUpload::where('file_hash', $fileHash)->first();
        if ($existing) {
            return back()->withErrors([
                'file' => "This exact file was already uploaded on {$existing->created_at->format('M d, Y')} (batch #{$existing->id}, status: {$existing->status}).",
            ]);
        }

        $storedPath = $file->store('dataset-uploads');

        $upload = DatasetUpload::create([
            'reporting_period_id' => $validated['reporting_period_id'],
            'uploaded_by' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => $fileHash,
            'status' => 'registered',
        ]);

        // Dispatched synchronously unless a queue worker is configured — fine for
        // dev/demo; for production-scale files this should run on a real queue.
        ProcessDatasetUpload::dispatch($upload, $storedPath);

        return redirect()->route('admin.uploads.show', $upload)
            ->with('status', 'Upload received and processed.');
    }

    public function show(DatasetUpload $upload)
    {
        $upload->load('reportingPeriod', 'uploader');

        $quarantined = $upload->records()
            ->where('validation_status', 'quarantined')
            ->with('findings', 'disease', 'barangay')
            ->paginate(20, ['*'], 'quarantined_page');

        $summary = [
            'blocking' => ValidationFinding::whereIn('surveillance_record_id', $upload->records()->pluck('id'))
                ->where('severity', 'blocking')->count(),
            'warning' => ValidationFinding::whereIn('surveillance_record_id', $upload->records()->pluck('id'))
                ->where('severity', 'warning')->count(),
            'informational' => ValidationFinding::whereIn('surveillance_record_id', $upload->records()->pluck('id'))
                ->where('severity', 'informational')->count(),
        ];

        return view('admin.uploads.show', compact('upload', 'quarantined', 'summary'));
    }

    /**
     * Show the correction form for a single quarantined row.
     */
    public function editRecord(DatasetUpload $upload, SurveillanceRecord $record)
    {
        $record->load('findings');
        return view('admin.uploads.edit-record', compact('upload', 'record'));
    }

    /**
     * Apply a correction to a quarantined row's raw data and revalidate just that row.
     */
    public function updateRecord(Request $request, DatasetUpload $upload, SurveillanceRecord $record, DatasetValidationService $validator)
    {
        $correctedRow = $request->validate([
            'disease' => 'nullable|string',
            'icd10_code' => 'nullable|string',
            'barangay' => 'nullable|string',
            'consultation_date' => 'nullable|string',
            'age' => 'nullable|string',
            'sex' => 'nullable|string',
        ]);

        DB::transaction(function () use ($correctedRow, $record, $validator, $upload) {
             $result = $validator->validateRow($correctedRow, $upload->reportingPeriod);

            // Clear old findings for this record, replace with fresh results.
            $record->findings()->delete();

            foreach ($result['findings'] as $finding) {
                ValidationFinding::create([
                    'surveillance_record_id' => $record->id,
                    'field' => $finding['field'],
                    'severity' => $finding['severity'],
                    'message' => $finding['message'],
                ]);
            }

            $record->update([
                'raw_data' => array_merge($record->raw_data ?? [], $correctedRow),
                'disease_id' => $result['standardized']['disease_id'],
                'icd10_code_id' => $result['standardized']['icd10_code_id'],
                'barangay_id' => $result['standardized']['barangay_id'],
                'consultation_date' => $result['standardized']['consultation_date'],
                'age' => $result['standardized']['age'],
                'sex' => $result['standardized']['sex'],
                'validation_status' => $result['has_blocking'] ? 'quarantined' : 'corrected',
            ]);
        });

        // Recompute batch-level counts.
        $upload->update([
            'accepted_rows' => $upload->records()->whereIn('validation_status', ['valid', 'corrected'])->count(),
            'quarantined_rows' => $upload->records()->where('validation_status', 'quarantined')->count(),
        ]);

        if ($upload->quarantined_rows === 0) {
            $upload->update(['status' => 'processed']);
        }

        return redirect()->route('admin.uploads.show', $upload)
            ->with('status', 'Row corrected and revalidated.');
    }
}
