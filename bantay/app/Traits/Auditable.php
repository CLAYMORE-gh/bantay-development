<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Add `use Auditable;` to any Eloquent model to automatically log every
 * create/update/delete to audit_logs, with before/after snapshots.
 *
 * Example models to add this to: User, SurveillanceRule, Alert, Disease,
 * Barangay, Icd10Code, ReportingPeriod — anything an Administrator's
 * actions should be traceable for.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'created',
                'auditable_type' => static::class,
                'auditable_id' => $model->id,
                'after' => $model->toArray(),
                'ip_address' => Request::ip(),
            ]);
        });

        static::updated(function ($model) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'auditable_type' => static::class,
                'auditable_id' => $model->id,
                'before' => $model->getOriginal(),
                'after' => $model->getChanges(),
                'ip_address' => Request::ip(),
            ]);
        });

        static::deleted(function ($model) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'auditable_type' => static::class,
                'auditable_id' => $model->id,
                'before' => $model->toArray(),
                'ip_address' => Request::ip(),
            ]);
        });
    }
}
