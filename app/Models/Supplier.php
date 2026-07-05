<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'company_name',
        'sales_name',
        'phone_number',
        'image_url',
        'image_public_id',
    ];
}
