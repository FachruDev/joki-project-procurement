<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_detail_page_shows_profile_and_uploaded_document(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'company_name' => 'Gallenium Supplies',
            'status' => VendorStatus::Approved,
        ]);

        $document = VendorDocument::factory()->create([
            'vendor_id' => $vendor->id,
            'document_type' => 'business_license',
        ]);
        $document->addMedia(UploadedFile::fake()->create('license.pdf', 120))
            ->toMediaCollection('documents');

        $response = $this->actingAs($admin)->get(route('vendor.show', $vendor));

        $response
            ->assertOk()
            ->assertSee('Gallenium Supplies')
            ->assertSee('business_license')
            ->assertSee('View File');
    }
}
