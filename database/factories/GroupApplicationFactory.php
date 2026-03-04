<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupApplicationFactory extends Factory
{
    protected $model = GroupApplication::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'full_name' => fake()->name(),
            'whatsapp_number' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'note' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
