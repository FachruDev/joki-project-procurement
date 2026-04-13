<?php

namespace Tests\Feature\Procurement;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\VendorStatus;
use Database\Seeders\ProcurementDemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_procurement_operational_data(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ProcurementDemoSeeder::class);

        $this->assertGreaterThanOrEqual(24, Vendor::query()->count());
        $this->assertGreaterThan(0, Vendor::query()->where('status', VendorStatus::Approved)->count());
        $this->assertGreaterThan(0, Vendor::query()->where('status', VendorStatus::Pending)->count());
        $this->assertGreaterThan(0, VendorDocument::query()->count());
        $this->assertGreaterThan(0, Rfq::query()->count());
        $this->assertGreaterThan(0, RfqResponse::query()->count());
        $this->assertGreaterThan(0, PurchaseOrder::query()->count());
        $this->assertGreaterThan(0, Invoice::query()->count());
        $this->assertGreaterThan(0, Invoice::query()->where('status', InvoiceStatus::Pending)->count());
        $this->assertNotNull(User::query()->where('email', 'superadmin@procurement.test')->first());
    }
}
