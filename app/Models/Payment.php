<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'cashier_id',
        'payment_method',
        'amount_paid',
        'change_amount',
        'reference_number',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount_paid'    => 'decimal:2',
        'change_amount'  => 'decimal:2',
        'paid_at'        => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
