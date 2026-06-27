<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'mutation_type',
        'quantity',
        'date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function productBatch()
    {
        return $this->hasOne(ProductBatch::class, 'stock_mutation_id');
    }
}
