<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory; 

    protected $fillable = [
        'owner_id', 'name', 'species', 'breed', 
        'gender', 'dob', 'color', 'distinctive_traits', 'allergies'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}