<?php

use App\Models\Invoice;
use App\Models\Appointment;

$appointment = Appointment::where('owner_id', 3)->first();
$appId = $appointment ? $appointment->id : 1; // dummy

$invoice = new Invoice();
$invoice->id = 'INV-' . date('Ymd') . '-007';
$invoice->appointment_id = $appId;
$invoice->owner_id = 3;
$invoice->cashier_id = 1;
$invoice->subtotal = 500000;
$invoice->discount = 0;
$invoice->total_amount = 500000;
$invoice->payment_method = 'QRIS';
$invoice->status = 'Unpaid';
$invoice->save();

$invoice->items()->create([
    'item_type' => 'Service',
    'item_id' => 1,
    'quantity' => 1,
    'price' => 500000,
    'subtotal' => 500000,
]);

echo "Success creating invoice for owner 3\n";
