<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveillanceRecord extends Model
{
    protected $fillable = [
        'dataset_upload_id', 'reporting_period_id', 'disease_id', 'icd10_code_id',
        'barangay_id', 'raw_data', 'consultation_date', 'age', 'sex',
        'row_fingerprint', 'validation_status',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'consultation_date' => 'date',
    ];

    public function datasetUpload()
    {
        return $this->belongsTo(DatasetUpload::class);
    }

    public function reportingPeriod()
    {
        return $this->belongsTo(ReportingPeriod::class);
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function icd10Code()
    {
        return $this->belongsTo(Icd10Code::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function findings()
    {
        return $this->hasMany(ValidationFinding::class);
    }

    public function blockingFindings()
    {
        return $this->findings()->where('severity', 'blocking')->where('resolved', false);
    }
}
