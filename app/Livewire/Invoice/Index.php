<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Invoice Upload')]
class Index extends Component
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
        $vendor = Auth::user()?->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $orders = PurchaseOrder::query()
            ->with(['invoice.media'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->get();

        return view('livewire.invoice.index', [
            'orders' => $orders,
        ]);
    }
}
