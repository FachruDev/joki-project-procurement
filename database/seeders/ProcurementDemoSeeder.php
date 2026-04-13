<?php

namespace Database\Seeders;

use App\InvoiceStatus;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\PurchaseOrderStatus;
use App\RfqStatus;
use App\VendorStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProcurementDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $procurementUsers = $this->createProcurementUsers();
        $vendors = $this->createVendors();
        $approvedVendors = $vendors->where('status', VendorStatus::Approved)->values();

        $this->createRfqsAndResponses($procurementUsers, $approvedVendors);
        $this->createPurchaseOrdersFromRfqs($procurementUsers);
        $this->createDirectPurchaseOrders($procurementUsers, $approvedVendors);
    }

    /**
     * @return Collection<int, User>
     */
    private function createProcurementUsers(): Collection
    {
        $existingOperationalUsers = User::query()
            ->role(['SuperAdmin', 'Admin', 'Procurement'])
            ->get();

        $newProcurementUsers = User::factory()
            ->count(2)
            ->create();

        foreach ($newProcurementUsers as $procurementUser) {
            $procurementUser->assignRole('Procurement');
        }

        return $existingOperationalUsers
            ->concat($newProcurementUsers)
            ->values();
    }

    /**
     * @return Collection<int, Vendor>
     */
    private function createVendors(): Collection
    {
        $vendors = collect();
        $documentTypes = [
            'business_license',
            'tax_card',
            'company_profile',
            'compliance_certificate',
        ];

        for ($index = 1; $index <= 24; $index++) {
            $vendorUser = User::factory()->create();
            $vendorUser->assignRole('Vendor');

            $status = match (true) {
                $index <= 14 => VendorStatus::Approved,
                $index <= 20 => VendorStatus::Pending,
                default => VendorStatus::Rejected,
            };

            $vendor = Vendor::create([
                'user_id' => $vendorUser->id,
                'company_name' => sprintf('Vendor %02d %s', $index, fake()->company()),
                'address' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'status' => $status,
            ]);

            $vendors->push($vendor);

            $documentCount = random_int(1, 3);
            for ($docIndex = 1; $docIndex <= $documentCount; $docIndex++) {
                VendorDocument::create([
                    'vendor_id' => $vendor->id,
                    'document_type' => $documentTypes[array_rand($documentTypes)],
                ]);
            }
        }

        return $vendors;
    }

    /**
     * @param  Collection<int, User>  $procurementUsers
     * @param  Collection<int, Vendor>  $approvedVendors
     */
    private function createRfqsAndResponses(Collection $procurementUsers, Collection $approvedVendors): void
    {
        $approvedVendorIds = $approvedVendors->pluck('id')->values();

        for ($index = 1; $index <= 18; $index++) {
            $isOpen = $index > 12;

            $rfq = Rfq::create([
                'title' => sprintf('RFQ-%03d %s', $index, fake()->words(3, true)),
                'description' => fake()->paragraphs(2, true),
                'deadline' => $isOpen ? now()->addDays(random_int(3, 20)) : now()->subDays(random_int(2, 18)),
                'created_by' => $procurementUsers->random()->id,
                'status' => $isOpen ? RfqStatus::Open : RfqStatus::Closed,
            ]);

            $assignedCount = min(random_int(3, 6), $approvedVendorIds->count());
            $assignedVendorIds = $approvedVendorIds
                ->shuffle()
                ->take($assignedCount)
                ->values();

            $rfq->vendors()->sync($assignedVendorIds->all());

            $minimumResponders = $isOpen ? 1 : max(2, intdiv($assignedCount, 2));
            $respondersCount = random_int($minimumResponders, $assignedCount);
            $respondingVendorIds = $assignedVendorIds
                ->shuffle()
                ->take($respondersCount)
                ->values();

            foreach ($respondingVendorIds as $vendorId) {
                RfqResponse::create([
                    'rfq_id' => $rfq->id,
                    'vendor_id' => $vendorId,
                    'price' => fake()->randomFloat(2, 3000, 250000),
                    'notes' => fake()->optional(0.6)->sentence(),
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $procurementUsers
     */
    private function createPurchaseOrdersFromRfqs(Collection $procurementUsers): void
    {
        $closedRfqs = Rfq::query()
            ->where('status', RfqStatus::Closed)
            ->with('responses')
            ->get();

        foreach ($closedRfqs as $closedRfq) {
            if ($closedRfq->responses->isEmpty() || random_int(1, 100) > 75) {
                continue;
            }

            $selectedResponse = $closedRfq->responses
                ->sortBy('price')
                ->first();

            if ($selectedResponse === null) {
                continue;
            }

            $purchaseOrderStatus = $this->randomPurchaseOrderStatus();

            $purchaseOrder = PurchaseOrder::create([
                'rfq_id' => $closedRfq->id,
                'vendor_id' => $selectedResponse->vendor_id,
                'total_price' => 0,
                'status' => $purchaseOrderStatus,
                'created_by' => $procurementUsers->random()->id,
            ]);

            $this->seedPurchaseOrderItems($purchaseOrder);
            $this->seedDeliveryAndInvoice($purchaseOrder, $selectedResponse->vendor_id);
        }
    }

    /**
     * @param  Collection<int, User>  $procurementUsers
     * @param  Collection<int, Vendor>  $approvedVendors
     */
    private function createDirectPurchaseOrders(Collection $procurementUsers, Collection $approvedVendors): void
    {
        for ($index = 1; $index <= 8; $index++) {
            $vendor = $approvedVendors->random();
            $purchaseOrderStatus = $this->randomPurchaseOrderStatus();

            $purchaseOrder = PurchaseOrder::create([
                'rfq_id' => null,
                'vendor_id' => $vendor->id,
                'total_price' => 0,
                'status' => $purchaseOrderStatus,
                'created_by' => $procurementUsers->random()->id,
            ]);

            $this->seedPurchaseOrderItems($purchaseOrder);
            $this->seedDeliveryAndInvoice($purchaseOrder, $vendor->id);
        }
    }

    private function seedPurchaseOrderItems(PurchaseOrder $purchaseOrder): void
    {
        $itemCount = random_int(2, 5);
        $totalPrice = 0;

        for ($index = 1; $index <= $itemCount; $index++) {
            $quantity = random_int(1, 40);
            $price = fake()->randomFloat(2, 100, 8000);
            $totalPrice += ($quantity * $price);

            PoItem::create([
                'po_id' => $purchaseOrder->id,
                'item_name' => fake()->words(3, true),
                'qty' => $quantity,
                'price' => $price,
            ]);
        }

        $purchaseOrder->update([
            'total_price' => round($totalPrice, 2),
        ]);
    }

    private function seedDeliveryAndInvoice(PurchaseOrder $purchaseOrder, int $vendorId): void
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::Completed && random_int(1, 100) <= 80) {
            Delivery::create([
                'po_id' => $purchaseOrder->id,
                'received_date' => now()->subDays(random_int(1, 15)),
                'notes' => fake()->optional(0.5)->sentence(),
            ]);
        }

        if (random_int(1, 100) > 90) {
            return;
        }

        $invoiceStatus = match ($purchaseOrder->status) {
            PurchaseOrderStatus::Draft => InvoiceStatus::Pending,
            PurchaseOrderStatus::Approved => fake()->randomElement([InvoiceStatus::Pending, InvoiceStatus::Approved]),
            PurchaseOrderStatus::Completed => fake()->randomElement([InvoiceStatus::Approved, InvoiceStatus::Pending, InvoiceStatus::Rejected]),
        };

        Invoice::create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendorId,
            'status' => $invoiceStatus,
        ]);
    }

    private function randomPurchaseOrderStatus(): PurchaseOrderStatus
    {
        return fake()->randomElement([
            PurchaseOrderStatus::Draft,
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Completed,
            PurchaseOrderStatus::Completed,
        ]);
    }
}
