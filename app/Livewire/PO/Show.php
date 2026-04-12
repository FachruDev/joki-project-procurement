<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
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
