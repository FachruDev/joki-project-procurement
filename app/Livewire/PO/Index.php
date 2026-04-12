<?php

namespace App\Livewire\PO;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Purchase Orders')]
class Index extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', PurchaseOrder::class);
    }

    public function render(): View
    {
        $user = Auth::user();

        $orders = PurchaseOrder::query()
            ->with(['vendor.user', 'rfq'])
            ->when(
                $user?->vendor !== null,
                fn ($query) => $query->where('vendor_id', $user->vendor->id),
            )
            ->latest()
            ->get();

        return view('livewire.po.index', [
            'orders' => $orders,
        ]);
    }
}
