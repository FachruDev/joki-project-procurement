<?php

namespace Tests\Feature\Procurement;

use App\Livewire\PO\Create as PoCreate;
use App\Livewire\PO\Index as PoIndex;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
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

    public function test_approved_purchase_order_cannot_be_edited_or_deleted(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $purchaseOrder = PurchaseOrder::factory()->create([
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $this->actingAs($procurement)
            ->get(route('pos.edit', $purchaseOrder))
            ->assertForbidden();

        Livewire::actingAs($procurement)
            ->test(PoIndex::class)
            ->call('deletePurchaseOrder', $purchaseOrder->id)
            ->assertForbidden();
    }

    public function test_draft_purchase_order_can_be_edited_and_deleted(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $purchaseOrder = PurchaseOrder::factory()->create([
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Draft,
        ]);

        $this->actingAs($procurement)
            ->get(route('pos.edit', $purchaseOrder))
            ->assertOk();

        Livewire::actingAs($procurement)
            ->test(PoIndex::class)
            ->call('deletePurchaseOrder', $purchaseOrder->id);

        $this->assertDatabaseMissing('purchase_orders', [
            'id' => $purchaseOrder->id,
        ]);
    }
}
