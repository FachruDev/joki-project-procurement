<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User Profile')]
class UserProfile extends Component
{
    public User $user;

    /**
     * Mount selected user profile.
     */
    public function mount(User $user): void
    {
        Gate::authorize('user.manage');

        $this->user = $user;
    }

    public function render(): View
    {
        $user = $this->user->load([
            'roles:id,name',
            'permissions:id,name',
            'vendor:id,user_id,company_name,status,address,phone',
        ]);

        $user->loadCount([
            'createdRfqs',
            'createdPurchaseOrders',
        ]);

        return view('livewire.admin.user-profile', [
            'user' => $user,
        ]);
    }
}
