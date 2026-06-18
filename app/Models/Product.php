<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function productBatches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
