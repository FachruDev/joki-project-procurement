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
    /**
     * @var list<int>
     */
    public array $selectedInvoiceIds = [];

    public bool $selectAll = false;

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
        $invoice = Invoice::query()->findOrFail($invoiceId);

        if (! $this->updateStatus($invoice, InvoiceStatus::Approved)) {
            return;
        }

        Flux::toast(variant: 'success', text: __('Invoice approved successfully.'));
    }

    /**
     * Reject an invoice.
     */
    public function reject(int $invoiceId): void
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);

        if (! $this->updateStatus($invoice, InvoiceStatus::Rejected)) {
            return;
        }

        Flux::toast(variant: 'success', text: __('Invoice rejected successfully.'));
    }

    /**
     * Bulk approve selected invoices.
     */
    public function bulkApprove(): void
    {
        $this->bulkUpdateStatus(InvoiceStatus::Approved);
    }

    /**
     * Bulk reject selected invoices.
     */
    public function bulkReject(): void
    {
        $this->bulkUpdateStatus(InvoiceStatus::Rejected);
    }

    /**
     * Toggle all pending invoices as selected.
     */
    public function updatedSelectAll(bool $checked): void
    {
        if (! $checked) {
            $this->selectedInvoiceIds = [];

            return;
        }

        $this->selectedInvoiceIds = Invoice::query()
            ->where('status', InvoiceStatus::Pending)
            ->pluck('id')
            ->all();
    }

    /**
     * Update invoice status.
     */
    private function updateStatus(Invoice $invoice, InvoiceStatus $status): bool
    {
        Gate::authorize('approve', $invoice);

        if (! $invoice->hasMedia('invoice-files')) {
            $this->addError('selectedInvoiceIds', __('Invoice #:id does not have an uploaded file.', ['id' => $invoice->id]));

            return false;
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
            actionUrl: route('pos.show', $invoice->purchaseOrder),
            actionLabel: __('View PO'),
            variant: $status === InvoiceStatus::Approved ? 'success' : 'warning',
        ));

        $this->selectedInvoiceIds = array_values(array_filter(
            $this->selectedInvoiceIds,
            fn (int $selectedId): bool => $selectedId !== $invoice->id,
        ));

        $this->selectAll = false;

        return true;
    }

    /**
     * Update selected invoices status in bulk.
     */
    private function bulkUpdateStatus(InvoiceStatus $status): void
    {
        if ($this->selectedInvoiceIds === []) {
            $this->addError('selectedInvoiceIds', __('Please select at least one invoice.'));

            return;
        }

        $updated = 0;
        $failed = 0;

        $invoices = Invoice::query()
            ->whereIn('id', $this->selectedInvoiceIds)
            ->where('status', InvoiceStatus::Pending)
            ->get();

        foreach ($invoices as $invoice) {
            if ($this->updateStatus($invoice, $status)) {
                $updated++;
            } else {
                $failed++;
            }
        }

        if ($updated > 0) {
            Flux::toast(
                variant: 'success',
                text: $status === InvoiceStatus::Approved
                    ? __(':count invoice(s) approved.', ['count' => $updated])
                    : __(':count invoice(s) rejected.', ['count' => $updated]),
            );
        }

        if ($failed > 0) {
            Flux::toast(variant: 'warning', text: __(':count invoice(s) could not be processed.', ['count' => $failed]));
        }
    }

    public function render(): View
    {
        $invoices = Invoice::query()
            ->with(['vendor.user', 'purchaseOrder', 'media'])
            ->where('status', InvoiceStatus::Pending)
            ->latest()
            ->get();

        return view('livewire.invoice.approve', [
            'invoices' => $invoices,
        ]);
    }
}
