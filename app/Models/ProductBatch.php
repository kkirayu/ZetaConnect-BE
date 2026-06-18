<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'stock',
        'exp_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
