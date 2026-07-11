<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DoctorSeeder::class,
            ClinicSettingSeeder::class,
            ServiceSeeder::class,
            PetSeeder::class,
            AppointmentSeeder::class,
            CustomScenarioSeeder::class,
        ]);
    }
}
