<?php

namespace App\Jobs;

use App\Imports\SurveillanceRecordsImport;
use App\Models\DatasetUpload;
use App\Models\SurveillanceRecord;
use App\Models\ValidationFinding;
use App\Services\DatasetValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessDatasetUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DatasetUpload $upload, public string $storedFilePath)
    {
    }

    public function handle(DatasetValidationService $validator): void
    {
        $this->upload->update(['status' => 'checking']);

        try {
            $import = new SurveillanceRecordsImport();
            Excel::import($import, Storage::path($this->storedFilePath));
            $rows = $import->rows;

            $this->upload->update([
                'status' => 'validating',
                'total_rows' => count($rows),
            ]);

            $accepted = 0;
            $quarantined = 0;

            // Chunk so a 50,000-row file doesn't blow memory / run as one giant transaction.
            foreach (array_chunk($rows, 500, true) as $chunk) {
                DB::transaction(function () use ($chunk, $validator, &$accepted, &$quarantined) {
                    foreach ($chunk as $row) {
                        $result = $validator->validateRow($row);
                        $fingerprint = $validator->fingerprint($result['standardized']);

                        $status = $result['has_blocking'] ? 'quarantined' : 'valid';

                        $record = SurveillanceRecord::create([
                            'dataset_upload_id' => $this->upload->id,
                            'reporting_period_id' => $this->upload->reporting_period_id,
                            'disease_id' => $result['standardized']['disease_id'],
                            'icd10_code_id' => $result['standardized']['icd10_code_id'],
                            'barangay_id' => $result['standardized']['barangay_id'],
                            'raw_data' => $row,
                            'consultation_date' => $result['standardized']['consultation_date'],
                            'age' => $result['standardized']['age'],
                            'sex' => $result['standardized']['sex'],
                            'row_fingerprint' => $fingerprint,
                            'validation_status' => $status,
                        ]);

                        foreach ($result['findings'] as $finding) {
                            ValidationFinding::create([
                                'surveillance_record_id' => $record->id,
                                'field' => $finding['field'],
                                'severity' => $finding['severity'],
                                'message' => $finding['message'],
                            ]);
                        }

                        $status === 'quarantined' ? $quarantined++ : $accepted++;
                    }
                });
            }

            // Content-level duplicate flagging: mark rows sharing a fingerprint
            // with an earlier row in the SAME upload as a warning (not blocking —
            // a human should decide whether it's a genuine repeat visit).
            $this->flagContentDuplicates();

            $finalStatus = $quarantined > 0
                ? ($accepted > 0 ? 'partially_accepted' : 'quarantined')
                : 'processed';

            $this->upload->update([
                'status' => $finalStatus,
                'accepted_rows' => $accepted,
                'quarantined_rows' => $quarantined,
            ]);
        } catch (\Throwable $e) {
            $this->upload->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function flagContentDuplicates(): void
    {
        $duplicateFingerprints = SurveillanceRecord::where('dataset_upload_id', $this->upload->id)
            ->select('row_fingerprint')
            ->groupBy('row_fingerprint')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('row_fingerprint');

        foreach ($duplicateFingerprints as $fingerprint) {
            $records = SurveillanceRecord::where('dataset_upload_id', $this->upload->id)
                ->where('row_fingerprint', $fingerprint)
                ->get();

            // Skip the first occurrence; flag the rest as possible duplicates.
            foreach ($records->skip(1) as $duplicate) {
                ValidationFinding::create([
                    'surveillance_record_id' => $duplicate->id,
                    'field' => 'row',
                    'severity' => 'warning',
                    'message' => 'This row appears to duplicate another row in the same upload (same disease, barangay, date, age, sex).',
                ]);
            }
        }
    }
}
