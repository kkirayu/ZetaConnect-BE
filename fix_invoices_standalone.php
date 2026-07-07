<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$appointments = \App\Models\Appointment::with('pet')
    ->whereHas('pet', function($q) { $q->where('owner_id', 3); })
    ->where('status', 'Selesai')
    ->get();

$count = 0;
foreach($appointments as $appt) {
    // Check if invoice exists
    $exists = \App\Models\Invoice::where('appointment_id', $appt->id)->exists();
    if(!$exists) {
        $today = now()->format('Ymd');
        $invCount = \App\Models\Invoice::whereDate('created_at', today())->count() + 1 + $count;
        $invoiceId = 'INV-' . $today . '-' . str_pad($invCount, 3, '0', STR_PAD_LEFT);
        
        $invoice = \App\Models\Invoice::create([
            'id' => $invoiceId,
            'appointment_id' => $appt->id,
            'owner_id' => 3,
            'cashier_id' => 1, // Assume cashier id 1 exists
            'subtotal' => 150000,
            'discount' => 0,
            'total_amount' => 150000,
            'payment_method' => 'Tunai',
            'status' => 'Unpaid'
        ]);
        
        \App\Models\InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'Service',
            'item_id' => 1,
            'quantity' => 1,
            'price' => 150000,
            'subtotal' => 150000
        ]);
        echo "Created invoice $invoiceId for appointment {$appt->id}\n";
        $count++;
    }
}
echo "Done generating missing invoices.\n";
