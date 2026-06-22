<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'supplier_id',
        'mutation_type',
        'quantity',
        'date'
    ];
}
