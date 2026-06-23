<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = \App\Models\User::where('role', 'Owner')->first() ?? \App\Models\User::first();

        if (!$owner) {
            return;
        }

        $pets = [
            [
                'owner_id' => $owner->id,
                'name' => 'Molly',
                'species' => 'Kucing',
                'breed' => 'Persian',
                'gender' => 'Betina',
                'dob' => '2023-01-15',
                'color' => 'Putih',
                'allergies' => 'Tidak ada',
            ],
            [
                'owner_id' => $owner->id,
                'name' => 'Buddy',
                'species' => 'Anjing',
                'breed' => 'Golden Retriever',
                'gender' => 'Jantan',
                'dob' => '2022-05-20',
                'color' => 'Emas',
                'allergies' => 'Alergi makanan tertentu',
            ],
        ];

        foreach ($pets as $pet) {
            \App\Models\Pet::create($pet);
        }
    }
}
