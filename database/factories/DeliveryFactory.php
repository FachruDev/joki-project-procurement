<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_id' => PurchaseOrder::factory(),
            'received_date' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
