<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = fake()->city();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'city' => $name,
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }
}
