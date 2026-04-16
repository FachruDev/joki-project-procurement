<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Management')]
class UserManagement extends Component
{
    use WithPagination;

    private const string SUPER_ADMIN_ROLE = 'SuperAdmin';

    public string $search = '';

    public int $perPage = 10;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('user.manage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Delete selected user.
     */
    public function deleteUser(int $userId): void
    {
        Gate::authorize('user.manage');

        $user = User::query()->findOrFail($userId);

        if ($user->hasRole(self::SUPER_ADMIN_ROLE)) {
            Flux::toast(variant: 'danger', text: __('SuperAdmin account cannot be deleted.'));

            return;
        }

        if ($user->id === auth()->id()) {
            Flux::toast(variant: 'danger', text: __('You cannot delete your own account.'));

            return;
        }

        $user->delete();

        Flux::toast(variant: 'success', text: __('User deleted successfully.'));
    }

    public function render(): View
    {
        $users = User::query()
            ->with(['roles:id,name', 'permissions:id,name', 'vendor:id,user_id,status,company_name'])
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(fn ($nestedQuery) => $nestedQuery
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')),
            )
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
