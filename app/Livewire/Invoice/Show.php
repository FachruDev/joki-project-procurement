<?php

namespace App\Livewire\Invoice;

use App\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Invoice Detail')]
class Show extends Component
{
    public Invoice $invoice;

    /**
     * Mount the component.
     */
    public function mount(Invoice $invoice): void
    {
        Gate::authorize('view', $invoice);

        $this->invoice = $invoice;
    }

    public function render(): View
    {
        $invoice = $this->invoice->load([
            'vendor.user',
            'purchaseOrder.items',
            'media',
        ]);

        return view('livewire.invoice.show', [
            'invoice' => $invoice,
            'canPrintPdf' => $invoice->status === InvoiceStatus::Approved,
        ]);
    }
}
