<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('gr.create');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Delivery $delivery): bool
    {
        return $user->can('po.view');
    }
}
