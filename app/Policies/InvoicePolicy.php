<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\VendorStatus;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->vendor !== null && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return $user->can('invoice.approve') || $user->can('invoice.upload') || $user->can('po.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->can('invoice.approve')) {
            return true;
        }

        return $user->vendor !== null && $invoice->vendor_id === $user->vendor->id;
    }

    /**
     * Determine whether the user can upload an invoice file.
     */
    public function upload(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! $user->can('invoice.upload')) {
            return false;
        }

        if ($user->vendor === null || $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return $purchaseOrder->vendor_id === $user->vendor->id;
    }

    /**
     * Determine whether the user can approve the invoice.
     */
    public function approve(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.approve');
    }
}
