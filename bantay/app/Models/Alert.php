<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'surveillance_rule_id', 'disease_id', 'barangay_id', 'reporting_period_id',
        'severity', 'triggered_value', 'threshold_value', 'status',
        'acknowledged_by', 'acknowledged_at', 'assigned_to', 'resolved_by', 'resolved_at', 'notes',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'triggered_value' => 'float',
        'threshold_value' => 'float',
    ];

    // Statuses that count as "still needs attention" for badge counts, etc.
    public const OPEN_STATUSES = ['open', 'acknowledged', 'assigned', 'under_review', 'escalated'];

    public function rule()
    {
        return $this->belongsTo(SurveillanceRule::class, 'surveillance_rule_id');
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function reportingPeriod()
    {
        return $this->belongsTo(ReportingPeriod::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function history()
    {
        return $this->hasMany(AlertHistory::class);
    }

    public function scopeOpenStatus($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function severityWeight(): int
    {
        return match ($this->severity) {
            'critical' => 3,
            'alert' => 2,
            'watch' => 1,
            default => 0,
        };
    }
}
