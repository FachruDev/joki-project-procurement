<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Vendor;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Purchase Order')]
class Edit extends Component
{
    public PurchaseOrder $purchaseOrder;

    public ?int $rfqId = null;

    public ?int $vendorId = null;

    /**
     * @var array<int, array{item_name: string, qty: int|string, price: int|string}>
     */
    public array $items = [];

    /**
     * Mount the component.
     */
    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('update', $purchaseOrder);

        $this->purchaseOrder = $purchaseOrder->load('items');
        $this->rfqId = $purchaseOrder->rfq_id;
        $this->vendorId = $purchaseOrder->vendor_id;
        $this->items = $purchaseOrder->items
            ->map(fn ($item): array => [
                'item_name' => $item->item_name,
                'qty' => $item->qty,
                'price' => (string) $item->price,
            ])
            ->all();
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
     * Persist purchase order updates.
     */
    public function save(): void
    {
        Gate::authorize('update', $this->purchaseOrder);

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

        if ($validated['rfqId'] !== null) {
            $rfq = Rfq::query()->findOrFail($validated['rfqId']);
            $isAssignedVendor = $rfq->vendors()->whereKey($validated['vendorId'])->exists();

            if (! $isAssignedVendor) {
                $this->addError('vendorId', __('Selected vendor is not assigned to this RFQ.'));

                return;
            }
        }

        $totalPrice = collect($validated['items'])
            ->sum(fn (array $item): float => ((float) $item['qty']) * ((float) $item['price']));

        $this->purchaseOrder->update([
            'rfq_id' => $validated['rfqId'],
            'vendor_id' => $validated['vendorId'],
            'total_price' => $totalPrice,
        ]);

        $this->purchaseOrder->items()->delete();

        foreach ($validated['items'] as $item) {
            $this->purchaseOrder->items()->create([
                'item_name' => $item['item_name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        Flux::toast(variant: 'success', text: __('Purchase order updated successfully.'));

        $this->redirect(route('pos.show', $this->purchaseOrder, absolute: false), navigate: true);
    }

    /**
     * Get RFQs that can be used to create PO.
     *
     * @return Collection<int, Rfq>
     */
    #[Computed]
    public function availableRfqs(): Collection
    {
        return Rfq::query()
            ->with('vendors.user')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get vendors available for current PO form state.
     *
     * @return Collection<int, Vendor>
     */
    #[Computed]
    public function availableVendors(): Collection
    {
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

        return Vendor::query()
            ->with('user')
            ->where('status', VendorStatus::Approved)
            ->orderBy('company_name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.po.edit');
    }
}
