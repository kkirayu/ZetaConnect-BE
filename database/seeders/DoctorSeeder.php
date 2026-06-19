<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\User;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ada satu user dokter agar constraint tidak error, atau kita buat user default
        $user = User::where('role', 'Dokter')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Default Dokter',
                'email' => 'dokter@zeta.com',
                'password' => bcrypt('password'),
                'role' => 'Dokter',
                'status' => 'Aktif',
            ]);
        }

        $doctors = [
            [
                'name' => "Drh. Ananda Pratama",
                'spesialisasi' => "Dokter Hewan Umum",
                'schedule_summary' => "Senin, Selasa, Rabu (08:00 - 15:00)",
                'days' => ["Senin", "Selasa", "Rabu"],
                'image' => "/src/assets/doctor_img/dokter2.webp"
            ],
            [
                'name' => "Drh. Budi Santoso, M.Vet",
                'spesialisasi' => "Ahli Bedah & Ortopedi",
                'schedule_summary' => "Kamis, Jumat, Sabtu (10:00 - 18:00)",
                'days' => ["Kamis", "Jumat", "Sabtu"],
                'image' => "/src/assets/doctor_img/dokter1.webp"
            ],
            [
                'name' => "Drh. Citra Lestari",
                'spesialisasi' => "Spesialis Penyakit Dalam",
                'schedule_summary' => "Senin, Rabu, Jumat (09:00 - 16:00)",
                'days' => ["Senin", "Rabu", "Jumat"],
                'image' => "/src/assets/doctor_img/dokter9.webp"
            ],
            [
                'name' => "Drh. Dimas Anggara",
                'spesialisasi' => "Perawatan Gigi & Mulut",
                'schedule_summary' => "Selasa, Kamis, Sabtu (13:00 - 20:00)",
                'days' => ["Selasa", "Kamis", "Sabtu"],
                'image' => "/src/assets/doctor_img/dokter3.webp"
            ],
            [
                'name' => "Drh. Elena Putri, M.Si",
                'spesialisasi' => "Dermatologi Veteriner",
                'schedule_summary' => "Rabu, Jumat, Minggu (08:00 - 14:00)",
                'days' => ["Rabu", "Jumat", "Minggu"],
                'image' => "/src/assets/doctor_img/dokter8.webp"
            ],
            [
                'name' => "Drh. Faisal Rahman",
                'spesialisasi' => "Dokter Hewan Umum",
                'schedule_summary' => "Kamis, Jumat, Sabtu, Minggu (08:00 - 16:00)",
                'days' => ["Kamis", "Jumat", "Sabtu", "Minggu"],
                'image' => "/src/assets/doctor_img/dokter5.webp"
            ],
            [
                'name' => "Drh. Gita Savitri",
                'spesialisasi' => "Spesialis Penyakit Dalam",
                'schedule_summary' => "Selasa, Kamis, Sabtu (10:00 - 17:00)",
                'days' => ["Selasa", "Kamis", "Sabtu"],
                'image' => "/src/assets/doctor_img/dokter7.webp"
            ],
            [
                'name' => "Drh. Hendi Saputra, Sp.B.Vet",
                'spesialisasi' => "Ahli Bedah & Ortopedi",
                'schedule_summary' => "Senin, Selasa, Rabu (12:00 - 20:00)",
                'days' => ["Senin", "Selasa", "Rabu"],
                'image' => "/src/assets/doctor_img/dokter4.webp"
            ],
            [
                'name' => "Drh. Indah Permatasari",
                'spesialisasi' => "Dokter Hewan Umum",
                'schedule_summary' => "Senin - Jumat (15:00 - 21:00)",
                'days' => ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"],
                'image' => "/src/assets/doctor_img/dokter6.webp"
            ]
        ];

        foreach ($doctors as $doc) {
            Doctor::create([
                'user_id' => $user->id,
                'name' => $doc['name'],
                'spesialisasi' => $doc['spesialisasi'],
                'image' => $doc['image'],
            ]);
        }
    }
}
