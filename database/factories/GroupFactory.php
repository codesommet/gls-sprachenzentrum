<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => 'Groupe ' . fake()->word(),
            'name_fr' => 'Groupe ' . fake()->word(),
            'level' => fake()->randomElement(['A1', 'A2', 'B1', 'B2']),
            'status' => 'active',
            'time_range' => '10h00-12h00',
        ];
    }
}
