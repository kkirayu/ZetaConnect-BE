<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    protected $fillable = [
        'cashier_id',
        'start_time',
        'end_time',
        'starting_cash',
        'system_revenue',
        'physical_cash',
        'status',
    ];

    protected $casts = [
        'start_time'     => 'datetime',
        'end_time'       => 'datetime',
        'starting_cash'  => 'decimal:2',
        'system_revenue' => 'decimal:2',
        'physical_cash'  => 'decimal:2',
    ];

    /**
     * Relasi ke User (Kasir).
     * cashier_id → users.id
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
