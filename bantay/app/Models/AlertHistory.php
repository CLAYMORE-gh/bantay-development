<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertHistory extends Model
{
    protected $fillable = ['alert_id', 'from_status', 'to_status', 'changed_by', 'notes'];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
