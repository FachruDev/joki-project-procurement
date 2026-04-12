<?php

namespace App\Livewire\Invoice;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Upload Invoice')]
class Upload extends Component
{
    use WithFileUploads;

    public PurchaseOrder $purchaseOrder;

    public mixed $invoiceFile = null;

    /**
     * Mount the component.
     */
    public function mount(PurchaseOrder $purchaseOrder): void
    {
        Gate::authorize('upload', [Invoice::class, $purchaseOrder]);

        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Upload the invoice file.
     */
    public function save(): void
    {
        Gate::authorize('upload', [Invoice::class, $this->purchaseOrder]);

        $validated = $this->validate([
            'invoiceFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = Auth::user()?->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $invoice = Invoice::query()->firstOrNew([
            'po_id' => $this->purchaseOrder->id,
        ]);

        $invoice->vendor_id = $vendor->id;
        $invoice->status = InvoiceStatus::Pending;
        $invoice->save();

        $invoice->clearMediaCollection('invoice-files');
        $invoice->addMedia($validated['invoiceFile'])->toMediaCollection('invoice-files');

        $this->reset('invoiceFile');

        Flux::toast(variant: 'success', text: __('Invoice uploaded successfully.'));

        $this->redirect(route('pos.show', $this->purchaseOrder, absolute: false), navigate: true);
    }

    public function render(): View
    {
        $existingInvoice = Invoice::query()
            ->with('media')
            ->where('po_id', $this->purchaseOrder->id)
            ->first();

        return view('livewire.invoice.upload', [
            'existingInvoice' => $existingInvoice,
        ]);
    }
}
