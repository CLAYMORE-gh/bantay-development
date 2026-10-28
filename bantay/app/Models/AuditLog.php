<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'before', 'after', 'ip_address'];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditableLabel(): string
    {
        if (!$this->auditable_type) {
            return '—';
        }
        return class_basename($this->auditable_type) . ' #' . $this->auditable_id;
    }
    public function detailsLabel(): string
    {
        if ($this->auditable_type) {
        return $this->auditableLabel();
        }
        return $this->after['title'] ?? $this->after['filename'] ?? '—';
    }
}
