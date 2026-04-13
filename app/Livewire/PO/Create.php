<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Notifications\InAppNotification;
use App\PurchaseOrderStatus;
use App\RfqStatus;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Purchase Order')]
class Create extends Component
{
    public ?int $rfqId = null;

    public ?int $vendorId = null;

    /**
     * @var array<int, array{item_name: string, qty: int|string, price: int|string}>
     */
    public array $items = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('create', PurchaseOrder::class);

        $this->items = [
            ['item_name' => '', 'qty' => 1, 'price' => ''],
        ];

        $requestedRfq = request()->integer('rfq');
        $requestedVendor = request()->integer('vendor');

        if ($requestedRfq > 0) {
            $this->rfqId = $requestedRfq;
        }

        if ($requestedVendor > 0) {
            $this->vendorId = $requestedVendor;
        }
    }

    /**
     * Add a PO line item.
     */
    public function addItem(): void
    {
        $this->items[] = ['item_name' => '', 'qty' => 1, 'price' => ''];
    }

    /**
     * Remove a PO line item.
     */
    public function removeItem(int $index): void
    {
        if (count($this->items) === 1) {
            return;
        }

        unset($this->items[$index]);

        $this->items = array_values($this->items);
    }

    /**
     * Persist the purchase order.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'rfqId' => ['nullable', 'integer', Rule::exists('rfqs', 'id')],
            'vendorId' => [
                'required',
                'integer',
                Rule::exists('vendors', 'id')->where(fn ($query) => $query->where('status', VendorStatus::Approved->value)),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $rfq = null;
        if ($validated['rfqId'] !== null) {
            $rfq = Rfq::query()->findOrFail($validated['rfqId']);

            $isAssignedVendor = $rfq->vendors()->whereKey($validated['vendorId'])->exists();
            if (! $isAssignedVendor) {
                $this->addError('vendorId', __('Selected vendor is not assigned to this RFQ.'));

                return;
            }
        }

        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $totalPrice = collect($validated['items'])
            ->sum(fn (array $item): float => ((float) $item['qty']) * ((float) $item['price']));

        $purchaseOrder = DB::transaction(function () use ($validated, $user, $rfq, $totalPrice): PurchaseOrder {
            $purchaseOrder = PurchaseOrder::create([
                'rfq_id' => $validated['rfqId'],
                'vendor_id' => $validated['vendorId'],
                'total_price' => $totalPrice,
                'status' => PurchaseOrderStatus::Draft,
                'created_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'item_name' => $item['item_name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
            }

            if ($rfq !== null) {
                $rfq->update(['status' => RfqStatus::Closed]);
            }

            return $purchaseOrder;
        });

        $purchaseOrder->loadMissing('vendor.user');
        $purchaseOrder->vendor->user?->notify(new InAppNotification(
            title: __('New Purchase Order'),
            message: __('Purchase Order #:po has been issued to your company.', [
                'po' => $purchaseOrder->id,
            ]),
            actionUrl: route('pos.show', $purchaseOrder, absolute: false),
            actionLabel: __('View PO'),
            variant: 'success',
        ));

        Flux::toast(variant: 'success', text: __('Purchase order created successfully.'));

        $this->redirect(route('pos.show', $purchaseOrder, absolute: false), navigate: true);
    }

    /**
     * Get RFQs that can be used to create PO.
     */
    #[Computed]
    public function availableRfqs(): Collection
    {
        return Rfq::query()
            ->with('vendors.user')
            ->where('status', RfqStatus::Open)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get vendors available for current PO form state.
     */
    #[Computed]
    public function availableVendors(): Collection
    {
        $query = Vendor::query()
            ->with('user')
            ->where('status', VendorStatus::Approved);

        if ($this->rfqId !== null) {
            $rfq = Rfq::query()->find($this->rfqId);

            if ($rfq !== null) {
                return $rfq->vendors()
                    ->where('status', VendorStatus::Approved)
                    ->with('user')
                    ->orderBy('company_name')
                    ->get();
            }
        }

        return $query->orderBy('company_name')->get();
    }

    public function render(): View
    {
        return view('livewire.po.create');
    }
}
