<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Purchase Order Details')]
class Show extends Component
{
    public PurchaseOrder $purchaseOrder;

    /**
     * Mount the component.
     */
    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('view', $purchaseOrder);

        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Delete purchase order.
     */
    public function deletePurchaseOrder(): void
    {
        Gate::authorize('delete', $this->purchaseOrder);

        $this->purchaseOrder->delete();

        Flux::toast(variant: 'success', text: __('Purchase order deleted successfully.'));

        $this->redirect(route('pos.my', absolute: false), navigate: true);
    }

    public function render(): View
    {
        $purchaseOrder = $this->purchaseOrder->load([
            'vendor.user',
            'rfq',
            'items',
            'delivery',
            'invoice.media',
        ]);

        return view('livewire.po.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }
}
