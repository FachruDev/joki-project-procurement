<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\VendorStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Invoice')]
class MyInvoice extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Invoice::class);
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $purchaseOrders = PurchaseOrder::query()
            ->with(['vendor.user', 'invoice.media'])
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

        $isApprovedVendor = $user->vendor?->status === VendorStatus::Approved;

        return view('livewire.invoice.my-invoice', [
            'purchaseOrders' => $purchaseOrders,
            'canUploadInvoice' => $user->can('invoice.upload') && $isApprovedVendor,
        ]);
    }
}
