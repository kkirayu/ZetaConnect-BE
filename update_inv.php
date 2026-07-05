<?php
$inv = \App\Models\Invoice::find('INV-001');
if ($inv) {
    $inv->status = 'Unpaid';
    $inv->save();
}

$apt = \App\Models\Appointment::find(1);
if ($apt) {
    $apt->status = 'Selesai';
    $apt->save();
}

echo "BERHASIL\n";
