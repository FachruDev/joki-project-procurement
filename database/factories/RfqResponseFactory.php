<?php

namespace Database\Factories;

use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqResponse>
 */
class RfqResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfq_id' => Rfq::factory(),
            'vendor_id' => Vendor::factory(),
            'price' => fake()->randomFloat(2, 100, 100000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
