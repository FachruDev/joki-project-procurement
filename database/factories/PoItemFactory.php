<?php

namespace Database\Factories;

use App\Models\PoItem;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoItem>
 */
class PoItemFactory extends Factory
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
            'item_name' => fake()->words(3, true),
            'qty' => fake()->numberBetween(1, 50),
            'price' => fake()->randomFloat(2, 10, 10000),
        ];
    }
}
