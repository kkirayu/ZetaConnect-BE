$appointments = \App\Models\Appointment::with('pet')->whereHas('pet', function($q) { $q->where('owner_id', 3); })->get();
echo 'Appointments for owner 3: ' . count($appointments) . "\n";
foreach($appointments as $a) {
    echo 'ID: ' . $a->id . ', Status: ' . $a->status . "\n";
}

$invoices = \App\Models\Invoice::where('owner_id', 3)->get();
echo 'Invoices for owner 3: ' . count($invoices) . "\n";
foreach($invoices as $i) {
    echo 'ID: ' . $i->id . ', Appt_ID: ' . $i->appointment_id . ', Status: ' . $i->status . "\n";
}
