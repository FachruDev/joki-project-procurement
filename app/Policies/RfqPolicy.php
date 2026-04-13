<?php

namespace App\Policies;

use App\Models\Rfq;
use App\Models\User;
use App\VendorStatus;

class RfqPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        return $user->can('rfq.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Rfq $rfq): bool
    {
        if (! $user->can('rfq.view')) {
            return false;
        }

        if ($this->shouldEnforceApprovedVendor($user) && $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($user->vendor === null || $user->can('vendor.manage')) {
            return true;
        }

        return $rfq->vendors()->whereKey($user->vendor->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('rfq.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rfq $rfq): bool
    {
        return $user->can('rfq.create');
    }

    /**
     * Determine whether approved vendor status should be enforced for this user.
     */
    private function shouldEnforceApprovedVendor(User $user): bool
    {
        return $user->vendor !== null && ! $user->can('vendor.manage');
    }
}
