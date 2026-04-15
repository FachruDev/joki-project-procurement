<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My PO')]
class MyPo extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', PurchaseOrder::class);
    }

    /**
     * Delete a purchase order.
     */
    public function deletePurchaseOrder(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        Gate::authorize('delete', $purchaseOrder);

        $purchaseOrder->delete();

        Flux::toast(variant: 'success', text: __('Purchase order deleted successfully.'));
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $orders = PurchaseOrder::query()
            ->with(['vendor.user', 'rfq'])
            ->when(
                $user->vendor !== null,
                fn ($query) => $query->where('vendor_id', $user->vendor->id),
            )
            ->when(
                $user->vendor === null && $user->hasRole('Procurement'),
                fn ($query) => $query->where('created_by', $user->id),
            )
            ->latest()
            ->get();

        return view('livewire.po.my-po', [
            'orders' => $orders,
        ]);
    }
}
