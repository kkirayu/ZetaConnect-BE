<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EPrescription extends Model
{
    protected $table = 'e_prescriptions';

    protected $fillable = [
        'medical_record_id',
        'product_id',
        'quantity',
        'instructions',
        'status',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
