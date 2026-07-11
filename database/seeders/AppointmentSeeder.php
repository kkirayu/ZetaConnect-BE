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
        $doctor = \App\Models\Doctor::first();
        $pet = Pet::first();
        $service = Service::first();

        if (!$owner || !$doctor || !$pet || !$service) {
            $this->command->warn('Missing prerequisites to seed Appointments. Please ensure Users (owner), Doctor, Pet, and Service exist.');
            return;
        }

        $today = Carbon::now()->toDateString();
        $tomorrow = Carbon::now()->addDay()->toDateString();
        $todayFormatted = Carbon::now()->format('Ymd');
        $tomorrowFormatted = Carbon::now()->addDay()->format('Ymd');

        $appointments = [
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => $today,
                'schedule_time' => '10:00:00',
                'initial_complaint' => 'Kucing muntah-muntah',
                'queue_number' => "Q-{$todayFormatted}-001",
                'status' => 'Menunggu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Walk-in',
                'schedule_date' => $today,
                'schedule_time' => '11:00:00',
                'initial_complaint' => 'Vaksinasi rutin',
                'queue_number' => "Q-{$todayFormatted}-002",
                'status' => 'Disetujui',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Online',
                'schedule_date' => $today,
                'schedule_time' => '13:00:00',
                'initial_complaint' => 'Pemeriksaan gigi',
                'queue_number' => "Q-{$todayFormatted}-003",
                'status' => 'Dalam Periksa',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'service_id' => $service->id,
                'doctor_id' => $doctor->doctor_id,
                'booking_type' => 'Walk-in',
                'schedule_date' => $tomorrow,
                'schedule_time' => '09:00:00',
                'initial_complaint' => 'Gatal-gatal pada kulit',
                'queue_number' => "Q-{$tomorrowFormatted}-001",
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
