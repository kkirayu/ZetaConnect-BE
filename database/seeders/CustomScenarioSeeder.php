<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomScenarioSeeder extends Seeder
{
    public function run()
    {
        $owner = User::where('role', 'Owner')->first();
        $admin = User::where('role', 'Admin')->first();
        $doctor = Doctor::first();
        $service = Service::first();

        if (!$owner || !$admin || !$doctor || !$service) {
            $this->command->warn('Prerequisites missing for CustomScenarioSeeder.');
            return;
        }

        // 1. Ensure owner has 3 pets
        $pets = Pet::where('owner_id', $owner->id)->get();
        while ($pets->count() < 3) {
            Pet::create([
                'owner_id' => $owner->id,
                'name' => 'Pet Baru ' . ($pets->count() + 1),
                'species' => 'Kucing',
                'breed' => 'Campuran',
                'gender' => 'Jantan',
                'dob' => '2023-01-01',
                'color' => 'Hitam',
                'allergies' => 'Tidak ada',
            ]);
            $pets = Pet::where('owner_id', $owner->id)->get();
        }

        $pet1 = $pets[0];
        $pet2 = $pets[1];
        $pet3 = $pets[2];

        // 2. 2 pets already have 2 medical records each
        // This requires 4 'Selesai' appointments
        $this->createMedicalRecordForPet($pet1, $owner, $doctor, $service, 'Selesai', 2);
        $this->createMedicalRecordForPet($pet2, $owner, $doctor, $service, 'Selesai', 2);

        // 3. 3 appointments with one status 'Menunggu'
        $today = Carbon::now();
        $todayStr = $today->format('Ymd');
        
        $newAppointments = [
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet3->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => $today->toDateString(),
                'schedule_time' => '14:00:00',
                'initial_complaint' => 'Vaksin',
                'queue_number' => "Q-{$todayStr}-101",
                'status' => 'Menunggu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet1->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Walk-in',
                'schedule_date' => $today->toDateString(),
                'schedule_time' => '15:00:00',
                'initial_complaint' => 'Kontrol rutin',
                'queue_number' => "Q-{$todayStr}-102",
                'status' => 'Disetujui',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet2->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => $today->toDateString(),
                'schedule_time' => '16:00:00',
                'initial_complaint' => 'Sakit perut',
                'queue_number' => "Q-{$todayStr}-103",
                'status' => 'Dalam Periksa',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($newAppointments as $app) {
            Appointment::create($app);
        }

        // 4. Payments: 2 Invoices (1 Paid, 1 Unpaid)
        // We will link them to some appointments, or leave appointment_id null if allowed
        // Let's create two specific appointments for these invoices just to be safe.
        $appPaid = Appointment::create([
            'owner_id' => $owner->id,
            'pet_id' => $pet1->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->doctor_id,
            'booking_type' => 'Walk-in',
            'schedule_date' => $today->toDateString(),
            'schedule_time' => '09:00:00',
            'initial_complaint' => 'Sudah selesai periksa',
            'queue_number' => "Q-{$todayStr}-104",
            'status' => 'Selesai',
        ]);

        $appUnpaid = Appointment::create([
            'owner_id' => $owner->id,
            'pet_id' => $pet2->id,
            'service_id' => $service->id,
            'doctor_id' => $doctor->doctor_id,
            'booking_type' => 'Walk-in',
            'schedule_date' => $today->toDateString(),
            'schedule_time' => '09:30:00',
            'initial_complaint' => 'Sudah selesai periksa 2',
            'queue_number' => "Q-{$todayStr}-105",
            'status' => 'Selesai',
        ]);

        // Invoice 1: Paid
        $invPaidId = "INV-{$todayStr}-001";
        DB::table('invoices')->insert([
            'id' => $invPaidId,
            'appointment_id' => $appPaid->id,
            'owner_id' => $owner->id,
            'cashier_id' => $admin->id,
            'subtotal' => 150000,
            'discount' => 0,
            'total_amount' => 150000,
            'payment_method' => 'Tunai',
            'status' => 'Paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payments')->insert([
            'invoice_id' => $invPaidId,
            'cashier_id' => $admin->id,
            'payment_method' => 'Tunai',
            'amount_paid' => 150000,
            'change_amount' => 0,
            'status' => 'Success',
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Invoice 2: Unpaid
        $invUnpaidId = "INV-{$todayStr}-002";
        DB::table('invoices')->insert([
            'id' => $invUnpaidId,
            'appointment_id' => $appUnpaid->id,
            'owner_id' => $owner->id,
            'cashier_id' => $admin->id,
            'subtotal' => 200000,
            'discount' => 0,
            'total_amount' => 200000,
            'payment_method' => 'Transfer',
            'status' => 'Unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Custom scenario seeded successfully!');
    }

    private function createMedicalRecordForPet($pet, $owner, $doctor, $service, $status, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $pastDate = Carbon::now()->subDays(rand(1, 30));
            
            $app = Appointment::create([
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => $pastDate->toDateString(),
                'schedule_time' => '10:00:00',
                'initial_complaint' => 'Sakit ' . $i,
                'queue_number' => "Q-{$pastDate->format('Ymd')}-" . rand(100, 999),
                'status' => $status,
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]);

            DB::table('medical_records')->insert([
                'appointment_id' => $app->id,
                'pet_id' => $pet->id,
                'doctor_id' => $doctor->user_id, // doctor_id in medical_records references users table
                'subjective' => 'Gejala ' . $i,
                'objective' => 'Pemeriksaan ' . $i,
                'diagnosis_dictionary_id' => null, // Optional, can leave null or set to a valid ID
                'plan' => 'Tindakan ' . $i,
                'weight' => rand(30, 100) / 10, // 3.0 to 10.0
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]);
        }
    }
}
