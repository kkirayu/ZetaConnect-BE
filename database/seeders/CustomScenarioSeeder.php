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

        // --- DASHBOARD MOCK DATA ---
        
        // 1. Add some extra pets with different species to populate "Jenis Pasien Terbanyak"
        $speciesList = ['Kucing', 'Anjing', 'Burung', 'Kelinci', 'Hamster'];
        $pets = [];
        for ($i = 0; $i < 10; $i++) {
            $pets[] = Pet::create([
                'owner_id' => $owner->id,
                'name' => 'Pet Dash ' . $i,
                'species' => $speciesList[array_rand($speciesList)],
                'breed' => 'Campuran',
                'gender' => ($i % 2 == 0) ? 'Jantan' : 'Betina',
                'dob' => Carbon::now()->subMonths(rand(6, 36))->toDateString(),
                'color' => 'Bervariasi',
                'allergies' => 'Tidak ada',
            ]);
        }

        // 2. Add appointments and revenue for the last 7 days (Statistik Pendapatan)
        for ($day = 7; $day >= 0; $day--) {
            $targetDate = Carbon::now()->subDays($day);
            $dateStr = $targetDate->format('Ymd');
            
            // Random number of appointments per day (1 to 5)
            $dailyApptCount = rand(1, 5);
            
            for ($j = 0; $j < $dailyApptCount; $j++) {
                $randomPet = $pets[array_rand($pets)];
                
                // Create Appointment
                $app = Appointment::create([
                    'owner_id' => $owner->id,
                    'pet_id' => $randomPet->id,
                    'service_id' => $service->id,
                    'doctor_id' => $doctor->doctor_id,
                    'booking_type' => ($j % 2 == 0) ? 'Online' : 'Walk-in',
                    'schedule_date' => $targetDate->toDateString(),
                    'schedule_time' => '10:00:00',
                    'initial_complaint' => 'Keluhan ' . $j,
                    'queue_number' => "Q-{$dateStr}-" . rand(100, 999),
                    'status' => 'Selesai', // Selesai so it's counted
                    'created_at' => $targetDate,
                    'updated_at' => $targetDate,
                ]);

                // Create Invoice & Payment to populate Revenue chart
                $amount = rand(100000, 500000);
                $invId = "INV-{$dateStr}-" . rand(1000, 9999) . $j;
                
                DB::table('invoices')->insert([
                    'id' => $invId,
                    'appointment_id' => $app->id,
                    'owner_id' => $owner->id,
                    'cashier_id' => $admin->id,
                    'subtotal' => $amount,
                    'discount' => 0,
                    'total_amount' => $amount,
                    'payment_method' => 'Tunai',
                    'status' => 'Paid',
                    'created_at' => $targetDate,
                    'updated_at' => $targetDate,
                ]);

                DB::table('payments')->insert([
                    'invoice_id' => $invId,
                    'cashier_id' => $admin->id,
                    'payment_method' => 'Tunai',
                    'amount_paid' => $amount,
                    'change_amount' => 0,
                    'status' => 'Success',
                    'paid_at' => $targetDate, // Vital for revenue chart
                    'created_at' => $targetDate,
                    'updated_at' => $targetDate,
                ]);
            }
        }

        // 3. Data Khusus untuk Hari Ini (Kunjungan Hari Ini & Total Pendapatan Hari Ini)
        // Let's add explicit current day appointments to make sure it shows up nicely.
        $todayStr = Carbon::now()->format('Ymd');
        $todayAppts = [
            ['status' => 'Menunggu', 'time' => '09:00:00'],
            ['status' => 'Disetujui', 'time' => '10:30:00'],
            ['status' => 'Dalam Periksa', 'time' => '11:00:00'],
        ];

        foreach ($todayAppts as $idx => $tAppt) {
            $randomPet = $pets[array_rand($pets)];
            Appointment::create([
                'owner_id' => $owner->id,
                'pet_id' => $randomPet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => Carbon::now()->toDateString(),
                'schedule_time' => $tAppt['time'],
                'initial_complaint' => 'Kunjungan hari ini',
                'queue_number' => "Q-{$todayStr}-9" . str_pad($idx, 2, '0', STR_PAD_LEFT),
                'status' => $tAppt['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('Dashboard mock data seeded successfully!');
    }
}
