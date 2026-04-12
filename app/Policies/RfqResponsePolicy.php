<?php

namespace App\Policies;

use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\User;
use App\RfqStatus;
use App\VendorStatus;

class RfqResponsePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('rfq.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RfqResponse $rfqResponse): bool
    {
        if (! $user->can('rfq.view')) {
            return false;
        }

        if ($user->vendor === null) {
            return true;
        }

        return $rfqResponse->vendor_id === $user->vendor->id;
    }

    /**
     * Determine whether the user can submit a response for an RFQ.
     */
    public function submit(User $user, Rfq $rfq): bool
    {
        if (! $user->can('rfq.respond')) {
            return false;
        }

        if ($user->vendor === null || $user->vendor->status !== VendorStatus::Approved) {
            return false;
        }

        if ($rfq->status !== RfqStatus::Open) {
            return false;
        }

        return $rfq->vendors()->whereKey($user->vendor->id)->exists();
    }
}
