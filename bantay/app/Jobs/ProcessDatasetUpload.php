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
        @set_time_limit(0); // large exports can outlast PHP's default 30 s web limit

        $upload = $this->upload;
        $upload->update(['status' => 'checking']);

        try {
            // ---- 1. Read the file (first sheet, whitelisted columns only) ----
            $import = new SurveillanceRecordsImport();
            Excel::import($import, Storage::path($this->storedFilePath));

            if ($import->missingRequired !== []) {
                $this->markFailed($upload, sprintf(
                    'The file is missing required column(s): %s. Columns found: %s.',
                    implode(', ', $import->missingRequired),
                    implode(', ', $import->foundHeaders) ?: 'none'
                ));

                return;
            }

            $upload->update(['status' => 'validating', 'total_rows' => count($import->rows)]);

            // ---- 2. Validate every row in memory (nothing is written yet) ----
            $period = $upload->reportingPeriod;
            $prepared = [];
            $skipped = 0;

            foreach ($import->rows as $row) {
                $result = $validator->validateRow($row, $period, true);

                if ($result['skip']) {
                    $skipped++;
                    continue;
                }

                $prepared[] = [
                    'row' => $row,
                    'result' => $result,
                    'fingerprint' => $validator->fingerprint($result['standardized']),
                ];
            }

            // ---- 3. Duplicate detection (content level) ----
            // (a) same case appearing twice inside this file
            // (b) same case already accepted in an EARLIER batch
            $earlier = $this->fingerprintsAcceptedInOtherBatches($upload, $prepared);
            $seenInFile = [];

            foreach ($prepared as $i => $p) {
                if ($p['result']['has_blocking']) {
                    continue;
                }
                $fp = $p['fingerprint'];

                if (isset($seenInFile[$fp])) {
                    $prepared[$i]['result']['findings'][] = [
                        'field' => 'row', 'severity' => 'warning',
                        'message' => 'Possible duplicate: same disease, barangay, date, age and sex as another row in this file.',
                    ];
                } elseif (isset($earlier[$fp])) {
                    $prepared[$i]['result']['findings'][] = [
                        'field' => 'row', 'severity' => 'warning',
                        'message' => 'Possible duplicate: same disease, barangay, date, age and sex as a record already accepted in an earlier upload.',
                    ];
                }
                $seenInFile[$fp] = true;
            }

            // ---- 4. Store everything or nothing ----
            // One transaction for the whole batch: if anything fails, nothing
            // from this upload remains in the database (no partial datasets).
            $accepted = 0;
            $quarantined = 0;

            DB::transaction(function () use ($prepared, $upload, &$accepted, &$quarantined) {
                $now = now();

                foreach ($prepared as $p) {
                    $s = $p['result']['standardized'];
                    $status = $p['result']['has_blocking'] ? 'quarantined' : 'valid';

                    $record = SurveillanceRecord::create([
                        'dataset_upload_id' => $upload->id,
                        'reporting_period_id' => $upload->reporting_period_id,
                        'disease_id' => $s['disease_id'],
                        'icd10_code_id' => $s['icd10_code_id'],
                        'barangay_id' => $s['barangay_id'],
                        'raw_data' => $p['row'],
                        'consultation_date' => $s['consultation_date'],
                        'age' => $s['age'],
                        'sex' => $s['sex'],
                        'row_fingerprint' => $p['fingerprint'],
                        'validation_status' => $status,
                    ]);

                    if ($p['result']['findings'] !== []) {
                        ValidationFinding::insert(array_map(fn ($f) => [
                            'surveillance_record_id' => $record->id,
                            'field' => $f['field'],
                            'severity' => $f['severity'],
                            'message' => $f['message'],
                            'resolved' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $p['result']['findings']));
                    }

                    $status === 'quarantined' ? $quarantined++ : $accepted++;
                }
            });

            // ---- 5. Final status + notes ----
            $notes = [];
            if ($import->discardedColumns !== []) {
                $notes[] = 'Columns not imported (not stored): ' . implode(', ', $import->discardedColumns) . '.';
            }
            if ($skipped > 0) {
                $notes[] = "{$skipped} row(s) skipped: no diagnosis recorded, or a non-disease encounter (e.g. ICD-10 chapter Z).";
            }

            $finalStatus = $quarantined > 0
                ? ($accepted > 0 ? 'partially_accepted' : 'quarantined')
                : 'processed';

            $upload->update([
                'status' => $finalStatus,
                'accepted_rows' => $accepted,
                'quarantined_rows' => $quarantined,
                'skipped_rows' => $skipped,
                'notes' => $notes ? implode(' ', $notes) : null,
            ]);

            $this->afterSuccess($upload, $quarantined);
        } catch (\Throwable $e) {
            // The transaction (if it had started) has already rolled back, so
            // no records from this upload exist. Record why and carry on.
            $this->markFailed($upload, $e->getMessage());
            report($e);
        } finally {
            // The original workbook can contain identifying columns. Only the
            // whitelisted fields were copied into the database, so the file
            // itself is removed unless you explicitly opt to keep it.
            if (!config('bantay.keep_uploaded_files', false)) {
                Storage::delete($this->storedFilePath);
            }
        }
    }

    /** @param array<int, array{result: array, fingerprint: string}> $prepared */
    private function fingerprintsAcceptedInOtherBatches(DatasetUpload $upload, array $prepared): array
    {
        $candidates = [];
        foreach ($prepared as $p) {
            if (!$p['result']['has_blocking']) {
                $candidates[$p['fingerprint']] = true;
            }
        }

        $found = [];
        foreach (array_chunk(array_keys($candidates), 1000) as $chunk) {
            $hits = SurveillanceRecord::where('dataset_upload_id', '!=', $upload->id)
                ->whereIn('validation_status', ['valid', 'corrected'])
                ->whereIn('row_fingerprint', $chunk)
                ->pluck('row_fingerprint');

            foreach ($hits as $fp) {
                $found[$fp] = true;
            }
        }

        return $found;
    }

    private function markFailed(DatasetUpload $upload, string $reason): void
    {
        $upload->update([
            'status' => 'failed',
            'failure_reason' => mb_substr($reason, 0, 1000),
        ]);
    }

    /** Side effects after the batch is safely committed. They can never fail the batch. */
    private function afterSuccess(DatasetUpload $upload, int $quarantined): void
    {
        try {
            if (class_exists(EvaluateSurveillanceRules::class) && $upload->reportingPeriod) {
                EvaluateSurveillanceRules::dispatch($upload->reportingPeriod);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $notification = \App\Notifications\UploadQuarantinedNotification::class;
            if ($quarantined > 0 && class_exists($notification) && $upload->uploader) {
                $upload->uploader->notify(new $notification($upload->fresh()));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
