<?php

namespace Database\Factories;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
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
            'vendor_id' => Vendor::factory(),
            'status' => InvoiceStatus::Pending,
        ];
    }
}
