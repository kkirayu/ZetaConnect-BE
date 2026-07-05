<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Pet;

$pets = [150001, 150002];
$doctorId = 2; // Assuming doctor ID 2 exists
$serviceId = 1; // Assuming service ID 1 exists

foreach ($pets as $petId) {
    // Cek apakah hewan ada
    $pet = Pet::find($petId);
    if (!$pet) {
        echo "Pet {$petId} not found\n";
        continue;
    }

    // Cari appointment yang belum selesai atau buat baru
    $appointment = Appointment::where('pet_id', $petId)->first();
    
    if (!$appointment) {
        $appointment = Appointment::create([
            'pet_id' => $petId,
            'doctor_id' => $doctorId,
            'service_id' => $serviceId,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => now()->format('H:i'),
            'status' => 'Selesai',
            'notes' => 'Pemeriksaan rutin'
        ]);
    } else {
        $appointment->update([
            'doctor_id' => $doctorId,
            'status' => 'Selesai'
        ]);
    }

    // Buat rekam medis jika belum ada untuk appointment ini
    $medRecord = MedicalRecord::where('appointment_id', $appointment->id)->first();
    
    if (!$medRecord) {
        MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'pet_id' => $petId,
            'doctor_id' => $doctorId,
            'subjective' => 'Hewan tampak lesu, nafsu makan berkurang sejak 2 hari yang lalu.',
            'objective' => 'Suhu tubuh normal (38.5 C), detak jantung normal, tidak ada tanda-tanda dehidrasi parah.',
            'assessment' => 'Indigesti ringan / Stress',
            'plan' => 'Diberikan vitamin dan obat penambah nafsu makan. Observasi 3 hari ke depan.',
            'weight' => rand(30, 60) / 10, // 3.0 to 6.0 kg
        ]);
        echo "Medical record created for pet {$petId}\n";
    } else {
        echo "Medical record already exists for pet {$petId}\n";
    }
}
