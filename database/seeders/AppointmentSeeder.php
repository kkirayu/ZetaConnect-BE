<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('role', 'Owner')->first() ?? User::first();
        $doctor = User::where('role', 'Dokter')->first() ?? User::skip(1)->first();
        $pet = Pet::first();
        $service = Service::first();

        if (!$owner || !$doctor || !$pet || !$service) {
            $this->command->warn('Missing prerequisites to seed Appointments. Please ensure Users (owner, doctor), Pet, and Service exist.');
            return;
        }

        $appointments = [
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'booking_type' => 'Online',
                'schedule_date' => Carbon::now()->toDateString(),
                'schedule_time' => '10:00:00',
                'initial_complaint' => 'Kucing muntah-muntah',
                'queue_number' => 'A001',
                'status' => 'Menunggu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'booking_type' => 'Walk-in',
                'schedule_date' => Carbon::now()->toDateString(),
                'schedule_time' => '11:00:00',
                'initial_complaint' => 'Vaksinasi rutin',
                'queue_number' => 'A002',
                'status' => 'Disetujui',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'booking_type' => 'Online',
                'schedule_date' => Carbon::now()->toDateString(),
                'schedule_time' => '13:00:00',
                'initial_complaint' => 'Pemeriksaan gigi',
                'queue_number' => 'A003',
                'status' => 'Dalam Periksa',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'booking_type' => 'Walk-in',
                'schedule_date' => Carbon::now()->toDateString(),
                'schedule_time' => '09:00:00',
                'initial_complaint' => 'Gatal-gatal pada kulit',
                'queue_number' => 'A004',
                'status' => 'Disetujui',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }
    }
}
