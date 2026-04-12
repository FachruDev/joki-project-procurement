<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('vendor.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.manage') || $user->vendor?->is($vendor) === true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('vendor.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.manage') || $user->vendor?->is($vendor) === true;
    }

    /**
     * Determine whether the user can approve a vendor profile.
     */
    public function approve(User $user, Vendor $vendor): bool
    {
        return $user->can('vendor.approve');
    }
}
