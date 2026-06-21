<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    protected $fillable = [
        'appointment_id',
        'pet_id',
        'doctor_id',
        'diagnosis_dictionary_id',
        'subjective',
        'objective',
        'plan',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(EPrescription::class);
    }

    public function inpatientRecord()
    {
        return $this->hasOne(InpatientRecord::class);
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(DiagnosisDictionary::class, 'diagnosis_dictionary_id');
    }

    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }
}
