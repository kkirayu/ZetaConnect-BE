<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InpatientRecord extends Model
{
    protected $fillable = [
        'medical_record_id',
        'cage_id',
        'admission_date',
        'estimated_discharge_date',
        'actual_discharge_date',
        'status',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function cage()
    {
        return $this->belongsTo(Cage::class);
    }
}
