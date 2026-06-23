<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    protected $fillable = [
        'clinic_name',
        'logo_url',
        'address',
        'phone_number',
        'email',
        'operational_hours',
    ];
}
