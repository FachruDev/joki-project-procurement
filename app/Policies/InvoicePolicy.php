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
        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return $user->can('invoice.approve') || $user->can('invoice.view') || $user->can('invoice.upload');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->can('invoice.approve') || $user->can('vendor.manage')) {
            return true;
        }

        if (! $user->can('invoice.view') && ! $user->can('invoice.upload')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($user->vendor !== null) {
            return $invoice->vendor_id === $user->vendor->id;
        }

        $invoice->loadMissing('purchaseOrder:id,created_by');

        return $invoice->purchaseOrder?->created_by === $user->id;
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

    /**
     * Determine whether approved vendor status should be enforced for this user.
     */
    private function shouldEnforceApprovedVendor(User $user): bool
    {
        return $user->vendor !== null && ! $user->can('vendor.manage');
    }
}
