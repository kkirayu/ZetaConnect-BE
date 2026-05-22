<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Konsultasi Umum', 'category' => 'Medis', 'price' => 50000],
            ['name' => 'Vaksin Rabies', 'category' => 'Vaksin', 'price' => 150000],
            ['name' => 'Grooming Kucing Besar', 'category' => 'Grooming', 'price' => 85000],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create($service);
        }
    }
}
