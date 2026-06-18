<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cage extends Model
{
    protected $fillable = [
        'cage_number',
        'size',
        'status',
    ];

    public function inpatientRecords()
    {
        return $this->hasMany(InpatientRecord::class);
    }
}
