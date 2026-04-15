<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\PurchaseOrderStatus;
use App\VendorStatus;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return $user->can('po.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! $user->can('po.view')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($user->vendor === null || $user->can('vendor.manage')) {
            return true;
        }

        return $purchaseOrder->vendor_id === $user->vendor->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (! $user->can('po.create')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! $user->can('po.update')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            return false;
        }

        if ($user->can('vendor.manage')) {
            return true;
        }

        return $purchaseOrder->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! $user->can('po.delete')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            return false;
        }

        if ($user->can('vendor.manage')) {
            return true;
        }

        return $purchaseOrder->created_by === $user->id;
    }

    /**
     * Determine whether the user can record goods receipt.
     */
    public function recordGoodsReceipt(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('gr.create');
    }

    /**
     * Determine whether approved vendor status should be enforced for this user.
     */
    private function shouldEnforceApprovedVendor(User $user): bool
    {
        return $user->vendor !== null && ! $user->can('vendor.manage');
    }
}
