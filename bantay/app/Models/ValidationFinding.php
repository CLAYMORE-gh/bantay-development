<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationFinding extends Model
{
    protected $fillable = ['surveillance_record_id', 'field', 'severity', 'message', 'resolved'];

    protected $casts = [
        'resolved' => 'boolean',
    ];

    public function record()
    {
        return $this->belongsTo(SurveillanceRecord::class, 'surveillance_record_id');
    }
}
