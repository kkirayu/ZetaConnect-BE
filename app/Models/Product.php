<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'base_price',
        'selling_price',
        'current_stock',
        'min_stock',
        'exp_date',
        'image_url',
        'image_public_id',
    ];

    protected $appends = ['is_expired', 'stock_status'];

    public function getIsExpiredAttribute()
    {
        return $this->exp_date && $this->exp_date < today()->toDateString();
    }

    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'Habis';
        } elseif ($this->current_stock <= $this->min_stock) {
            return 'Hampir Habis';
        }
        return 'Tersedia';
    }

    public function productBatches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
