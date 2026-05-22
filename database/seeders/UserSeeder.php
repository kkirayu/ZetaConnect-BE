<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\User::create([
        'name' => 'Muhammad Danil',
        'email' => 'danil@zeta.com',
        'password' => bcrypt('password123'),
        'phone_number' => '08123456789',
        'role' => 'Admin', 
        'status' => 'Aktif',
        'address' => 'Yogyakarta',
    ]);

    \App\Models\User::create([
        'name' => 'Dr. Budi Santoso',
        'email' => 'budi@zeta.com',
        'password' => bcrypt('password123'),
        'phone_number' => '08987654321',
        'role' => 'Dokter',
        'status' => 'Aktif',
        'address' => 'Sleman, DIY',
    ]);
}
}
