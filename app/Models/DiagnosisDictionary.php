<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosisDictionary extends Model
{
    protected $table = 'diagnosis_dictionary';

    protected $fillable = [
        'disease_name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
