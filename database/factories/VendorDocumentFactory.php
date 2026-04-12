<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorDocument>
 */
class VendorDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'document_type' => fake()->randomElement(['business_license', 'tax_card', 'compliance_certificate']),
        ];
    }
}
