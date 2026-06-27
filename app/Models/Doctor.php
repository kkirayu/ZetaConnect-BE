<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $primaryKey = 'doctor_id';

    protected $fillable = [
        'user_id',
        'name',
        'image',
    ];

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'doctor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
