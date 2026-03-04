<?php

namespace Database\Factories;

use App\Models\GlsInscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class GlsInscriptionFactory extends Factory
{
    protected $model = GlsInscription::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'adresse' => fake()->address(),
            'niveau' => fake()->randomElement(['A1', 'A2', 'B1', 'B2']),
            'type_cours' => fake()->randomElement(['presentiel', 'en_ligne']),
            'centre' => 1,
            'form_source' => 'page',
        ];
    }
}
