<?php

namespace Database\Factories;

use App\Models\Rfq;
use App\Models\User;
use App\RfqStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rfq>
 */
class RfqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'deadline' => now()->addDays(fake()->numberBetween(2, 14)),
            'created_by' => User::factory(),
            'status' => RfqStatus::Open,
        ];
    }
}
