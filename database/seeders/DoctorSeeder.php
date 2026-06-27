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
                'schedule_summary' => "Senin, Selasa, Rabu (Sesi 1 - Sesi 7)",
                'days' => ["Senin", "Selasa", "Rabu"],
                'sessions' => ["Sesi 1", "Sesi 2", "Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7"],
                'image' => "/src/assets/doctor_img/dokter2.webp"
            ],
            [
                'name' => "Drh. Budi Santoso, M.Vet",
                'schedule_summary' => "Kamis, Jumat, Sabtu (Sesi 3 - Sesi 8)",
                'days' => ["Kamis", "Jumat", "Sabtu"],
                'sessions' => ["Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter1.webp"
            ],
            [
                'name' => "Drh. Citra Lestari",
                'schedule_summary' => "Senin, Rabu, Jumat (Sesi 2 - Sesi 8)",
                'days' => ["Senin", "Rabu", "Jumat"],
                'sessions' => ["Sesi 2", "Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter9.webp"
            ],
            [
                'name' => "Drh. Dimas Anggara",
                'schedule_summary' => "Selasa, Kamis, Sabtu (Sesi 6 - Sesi 8)",
                'days' => ["Selasa", "Kamis", "Sabtu"],
                'sessions' => ["Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter3.webp"
            ],
            [
                'name' => "Drh. Elena Putri, M.Si",
                'schedule_summary' => "Rabu, Jumat, Minggu (Sesi 1 - Sesi 7)",
                'days' => ["Rabu", "Jumat", "Minggu"],
                'sessions' => ["Sesi 1", "Sesi 2", "Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7"],
                'image' => "/src/assets/doctor_img/dokter8.webp"
            ],
            [
                'name' => "Drh. Faisal Rahman",
                'schedule_summary' => "Kamis, Jumat, Sabtu, Minggu (Sesi 1 - Sesi 8)",
                'days' => ["Kamis", "Jumat", "Sabtu", "Minggu"],
                'sessions' => ["Sesi 1", "Sesi 2", "Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter5.webp"
            ],
            [
                'name' => "Drh. Gita Savitri",
                'schedule_summary' => "Selasa, Kamis, Sabtu (Sesi 3 - Sesi 8)",
                'days' => ["Selasa", "Kamis", "Sabtu"],
                'sessions' => ["Sesi 3", "Sesi 4", "Sesi 5", "Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter7.webp"
            ],
            [
                'name' => "Drh. Hendi Saputra, Sp.B.Vet",
                'schedule_summary' => "Senin, Selasa, Rabu (Sesi 5 - Sesi 8)",
                'days' => ["Senin", "Selasa", "Rabu"],
                'sessions' => ["Sesi 5", "Sesi 6", "Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter4.webp"
            ],
            [
                'name' => "Drh. Indah Permatasari",
                'schedule_summary' => "Senin - Jumat (Sesi 7 - Sesi 8)",
                'days' => ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"],
                'sessions' => ["Sesi 7", "Sesi 8"],
                'image' => "/src/assets/doctor_img/dokter6.webp"
            ]
        ];

        foreach ($doctors as $doc) {
            $createdDoctor = Doctor::create([
                'user_id' => $user->id,
                'name' => $doc['name'],
                'image' => $doc['image'],
            ]);

            foreach ($doc['days'] as $day) {
                foreach ($doc['sessions'] as $session) {
                    \App\Models\DoctorSchedule::create([
                        'doctor_id' => $createdDoctor->doctor_id,
                        'hari_praktik' => $day,
                        'sesi_praktik' => $session
                    ]);
                }
            }
        }
    }
}
