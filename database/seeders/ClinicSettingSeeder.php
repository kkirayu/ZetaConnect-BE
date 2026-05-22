<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\ClinicSetting::create([
        'clinic_name' => 'Zeta Connect VetCare',
        'address' => 'Jl. Ring Road Utara, Yogyakarta',
        'phone_number' => '0274-123456',
        'email' => 'contact@zetaconnect.com',
        'operational_hours' => 'Senin - Sabtu: 08:00 - 20:00',
    ]);
}
}
