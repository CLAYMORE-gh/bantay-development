<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    use HasFactory;

    protected $table = 'icd10_codes';

    protected $fillable = ['code', 'description', 'disease_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
