<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveillanceRule extends Model
{
    protected $fillable = [
        'name', 'disease_id', 'threshold_type', 'threshold_value',
        'severity', 'priority', 'effective_date', 'is_active', 'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
        'threshold_value' => 'float',
    ];

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('effective_date', '<=', now());
    }

    public function thresholdTypeLabel(): string
    {
        return match ($this->threshold_type) {
            'absolute' => "Case count \u{2265} {$this->threshold_value}",
            'percentage_increase' => "Increase \u{2265} {$this->threshold_value}% vs previous period",
            'consecutive_increase' => "Rising for \u{2265} {$this->threshold_value} consecutive periods",
            'geographic_spread' => "Present in \u{2265} {$this->threshold_value} barangays",
            default => $this->threshold_type,
        };
    }
}
