<?php

namespace App\Livewire\GR;

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use App\PurchaseOrderStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Record Goods Receipt')]
class Create extends Component
{
    public PurchaseOrder $purchaseOrder;

    public string $receivedDate = '';

    public ?string $notes = null;

    /**
     * Mount the component.
     */
    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('recordGoodsReceipt', $purchaseOrder);

        $this->purchaseOrder = $purchaseOrder;
        $this->receivedDate = now()->format('Y-m-d\TH:i');
    }

    /**
     * Persist goods receipt.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'receivedDate' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Delivery::updateOrCreate([
            'po_id' => $this->purchaseOrder->id,
        ], [
            'received_date' => $validated['receivedDate'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->purchaseOrder->update([
            'status' => PurchaseOrderStatus::Completed,
        ]);

        Flux::toast(variant: 'success', text: __('Goods receipt recorded.'));

        $this->redirect(route('pos.show', $this->purchaseOrder, absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.gr.create');
    }
}
