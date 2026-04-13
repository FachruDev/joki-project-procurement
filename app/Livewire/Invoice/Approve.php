<?php

namespace App\Livewire\Invoice;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Notifications\InAppNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Invoice Approval')]
class Approve extends Component
{
    public string $statusFilter = 'pending';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Invoice::class);
    }

    /**
     * Approve an invoice.
     */
    public function approve(int $invoiceId): void
    {
        $this->updateStatus($invoiceId, InvoiceStatus::Approved);
    }

    /**
     * Reject an invoice.
     */
    public function reject(int $invoiceId): void
    {
        $this->updateStatus($invoiceId, InvoiceStatus::Rejected);
    }

    /**
     * Update invoice status.
     */
    private function updateStatus(int $invoiceId, InvoiceStatus $status): void
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);

        Gate::authorize('approve', $invoice);

        if (! $invoice->hasMedia('invoice-files')) {
            $this->addError('statusFilter', __('Invoice file must be uploaded before approval.'));

            return;
        }

        $invoice->update([
            'status' => $status,
        ]);

        $invoice->loadMissing('vendor.user', 'purchaseOrder');
        $invoice->vendor->user?->notify(new InAppNotification(
            title: $status === InvoiceStatus::Approved ? __('Invoice Approved') : __('Invoice Rejected'),
            message: $status === InvoiceStatus::Approved
                ? __('Your invoice for Purchase Order #:po has been approved.', ['po' => $invoice->po_id])
                : __('Your invoice for Purchase Order #:po has been rejected. Please review and upload an updated invoice.', ['po' => $invoice->po_id]),
            actionUrl: route('pos.show', $invoice->purchaseOrder, absolute: false),
            actionLabel: __('View PO'),
            variant: $status === InvoiceStatus::Approved ? 'success' : 'warning',
        ));

        Flux::toast(variant: 'success', text: __('Invoice status has been updated.'));
    }

    public function render(): View
    {
        $invoices = Invoice::query()
            ->with(['vendor.user', 'purchaseOrder', 'media'])
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->latest()
            ->get();

        return view('livewire.invoice.approve', [
            'invoices' => $invoices,
        ]);
    }
}
