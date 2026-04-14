<?php

namespace App\Livewire\Vendor;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Vendor Detail')]
class Show extends Component
{
    public Vendor $vendor;

    /**
     * Mount the component.
     */
    public function mount(Vendor $vendor): void
    {
        Gate::authorize('view', $vendor);

        $this->vendor = $vendor;
    }

    public function render(): View
    {
        $vendor = $this->vendor->load([
            'user:id,name,email',
            'documents.media',
        ]);

        $approvedInvoices = Invoice::query()
            ->with(['purchaseOrder:id,total_price', 'media'])
            ->where('vendor_id', $vendor->id)
            ->where('status', InvoiceStatus::Approved)
            ->latest()
            ->get();

        return view('livewire.vendor.show', [
            'vendor' => $vendor,
            'approvedInvoices' => $approvedInvoices,
        ]);
    }
}
