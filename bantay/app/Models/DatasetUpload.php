<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatasetUpload extends Model
{
    protected $fillable = [
        'reporting_period_id', 'uploaded_by', 'original_filename', 'file_hash',
        'status', 'total_rows', 'accepted_rows', 'quarantined_rows', 'skipped_rows',
        'notes', 'failure_reason',
    ];

    public function reportingPeriod()
    {
        return $this->belongsTo(ReportingPeriod::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function records()
    {
        return $this->hasMany(SurveillanceRecord::class);
    }

    public function quarantinedRecords()
    {
        return $this->records()->where('validation_status', 'quarantined');
    }
}
