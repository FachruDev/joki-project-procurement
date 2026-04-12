<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfq_id' => null,
            'vendor_id' => Vendor::factory(),
            'total_price' => fake()->randomFloat(2, 1000, 50000),
            'status' => PurchaseOrderStatus::Draft,
            'created_by' => User::factory(),
        ];
    }
}
