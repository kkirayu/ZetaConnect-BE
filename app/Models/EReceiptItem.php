<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EReceiptItem extends Model
{
    protected $table = 'e_receipt_items';

    protected $fillable = [
        'e_receipt_id',
        'medicine_name',
        'dosage',
        'frequency',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(EReceipt::class, 'e_receipt_id');
    }
}
