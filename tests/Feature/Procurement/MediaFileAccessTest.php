<?php

namespace Tests\Feature\Procurement;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\PurchaseOrderStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaFileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_open_own_invoice_media_through_media_route(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('public');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoice = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $invoice->addMedia(UploadedFile::fake()->create('invoice.pdf', 120))
            ->toMediaCollection('invoice-files');

        $media = $invoice->getFirstMedia('invoice-files');
        $this->assertNotNull($media);

        $this->actingAs($vendorUser)
            ->get(route('media.show', $media))
            ->assertOk();
    }

    public function test_user_without_vendor_access_cannot_open_vendor_document_media(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('public');

        $vendorOwner = User::factory()->create();
        $vendorOwner->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorOwner->id,
            'status' => VendorStatus::Approved,
        ]);

        $vendorDocument = VendorDocument::factory()->create([
            'vendor_id' => $vendor->id,
            'document_type' => 'company_profile',
        ]);

        $vendorDocument->addMedia(UploadedFile::fake()->create('company-profile.pdf', 120))
            ->toMediaCollection('documents');

        $media = $vendorDocument->getFirstMedia('documents');
        $this->assertNotNull($media);

        $otherVendorUser = User::factory()->create();
        $otherVendorUser->assignRole('Vendor');
        Vendor::factory()->create([
            'user_id' => $otherVendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $this->actingAs($otherVendorUser)
            ->get(route('media.show', $media))
            ->assertForbidden();
    }
}
