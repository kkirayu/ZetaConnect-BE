<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'danil@zeta.com'],
            [
                'name' => 'Muhammad Danil',
                'password' => bcrypt('password123'),
                'phone_number' => '08123456789',
                'role' => 'Admin', 
                'status' => 'Aktif',
                'address' => 'Yogyakarta',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'budi@zeta.com'],
            [
                'name' => 'Dr. Budi Santoso',
                'password' => bcrypt('password123'),
                'phone_number' => '08987654321',
                'role' => 'Dokter',
                'status' => 'Aktif',
                'address' => 'Sleman, DIY',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'pasien@zeta.com'],
            [
                'name' => 'Pasien Pemilik',
                'password' => bcrypt('password123'),
                'phone_number' => '08111222333',
                'role' => 'Owner',
                'status' => 'Aktif',
                'address' => 'Bantul, DIY',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'resepsionis@zeta.com'],
            [
                'name' => 'Resepsionis Zeta',
                'password' => bcrypt('password123'),
                'phone_number' => '08222333444',
                'role' => 'Resepsionis', 
                'status' => 'Aktif',
                'address' => 'Klinik Zeta Connect',
            ]
        );
    }
}
