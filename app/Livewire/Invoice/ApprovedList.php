<?php

namespace App\Livewire\Invoice;

use App\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Approved Invoices')]
class ApprovedList extends Component
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

        $invoices = Invoice::query()
            ->with(['purchaseOrder:id,total_price', 'media'])
            ->where('vendor_id', $vendor->id)
            ->where('status', InvoiceStatus::Approved)
            ->latest()
            ->get();

        return view('livewire.invoice.approved-list', [
            'invoices' => $invoices,
        ]);
    }
}
