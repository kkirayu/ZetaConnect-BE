<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'stock_mutation_id',
        'batch_number',
        'stock',
        'exp_date',
        'notes',
    ];

    public function stockMutation()
    {
        return $this->belongsTo(StockMutation::class, 'stock_mutation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
