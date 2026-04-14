<?php

namespace App\Http\Controllers;

use App\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoicePrintController extends Controller
{
    /**
     * Show print-friendly invoice page.
     */
    public function __invoke(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        if ($invoice->status !== InvoiceStatus::Approved) {
            abort(403, 'Invoice can only be printed after approval.');
        }

        $invoice->load([
            'vendor.user',
            'purchaseOrder.items',
            'media',
        ]);

        return view('invoices.print', [
            'invoice' => $invoice,
        ]);
    }
}
