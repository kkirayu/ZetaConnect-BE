<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Nama model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Pet::class;

    /**
     * Definisikan state default untuk model Pet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Menghubungkan otomatis ke User (Owner) yang ada atau membuat baru
            'owner_id' => User::where('role', 'Owner')->inRandomOrder()->first()?->id ?? User::factory(),
            
            'name' => $this->faker->firstName(),
            
            // Sesuai dengan Enum SpeciesType
            'species' => $this->faker->randomElement(['Kucing', 'Anjing', 'Burung', 'Lainnya']),
            
            'breed' => $this->faker->words(1, true),
            
            // Sesuai dengan Enum GenderType
            'gender' => $this->faker->randomElement(['Jantan', 'Betina']),
            
            'dob' => $this->faker->date('Y-m-d', 'now'),
            'color' => $this->faker->safeColorName(),
            
            'distinctive_traits' => $this->faker->sentence(),
            'allergies' => $this->faker->randomElement([
                'Tidak ada',
                'Alergi ayam',
                'Alergi seafood',
                'Sensitif terhadap debu',
                null
            ]),
        ];
    }
}