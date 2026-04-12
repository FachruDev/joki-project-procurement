<?php

namespace Tests\Feature\Procurement;

use App\Livewire\PO\Create as PoCreate;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_requires_at_least_one_item(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendor = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        Livewire::actingAs($procurement)
            ->test(PoCreate::class)
            ->set('vendorId', $vendor->id)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items' => 'required']);
    }

    public function test_procurement_can_create_purchase_order_with_items(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendor = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        Livewire::actingAs($procurement)
            ->test(PoCreate::class)
            ->set('vendorId', $vendor->id)
            ->set('items', [
                ['item_name' => 'Laptop', 'qty' => 2, 'price' => 1000],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $purchaseOrder = PurchaseOrder::query()->first();

        $this->assertNotNull($purchaseOrder);
        $this->assertDatabaseHas('po_items', [
            'po_id' => $purchaseOrder->id,
            'item_name' => 'Laptop',
            'qty' => 2,
        ]);
    }
}
