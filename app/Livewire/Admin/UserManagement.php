<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('User Management')]
class UserManagement extends Component
{
    public string $search = '';

    public ?int $selectedUserId = null;

    /**
     * @var list<int>
     */
    public array $selectedRoleIds = [];

    /**
     * @var list<int>
     */
    public array $selectedPermissionIds = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('user.manage');
    }

    /**
     * Select a user and load current role/permission assignments.
     */
    public function selectUser(int $userId): void
    {
        Gate::authorize('user.manage');

        $user = User::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->findOrFail($userId);

        $this->selectedUserId = $user->id;
        $this->selectedRoleIds = $user->roles->pluck('id')->all();
        $this->selectedPermissionIds = $user->permissions->pluck('id')->all();
    }

    /**
     * Save role and direct permission assignment for selected user.
     */
    public function saveAssignments(): void
    {
        Gate::authorize('user.manage');

        $validated = $this->validate([
            'selectedUserId' => ['required', 'integer', Rule::exists('users', 'id')],
            'selectedRoleIds' => ['nullable', 'array'],
            'selectedRoleIds.*' => ['integer', Rule::exists('roles', 'id')],
            'selectedPermissionIds' => ['nullable', 'array'],
            'selectedPermissionIds.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $user = User::query()->findOrFail($validated['selectedUserId']);

        $roleNames = Role::query()
            ->whereIn('id', $validated['selectedRoleIds'] ?? [])
            ->pluck('name')
            ->all();

        $permissionNames = Permission::query()
            ->whereIn('id', $validated['selectedPermissionIds'] ?? [])
            ->pluck('name')
            ->all();

        $user->syncRoles($roleNames);
        $user->syncPermissions($permissionNames);

        Flux::toast(variant: 'success', text: __('User role and permission updated.'));
    }

    /**
     * Get users list.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(fn ($nested) => $nested
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')),
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all available roles.
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    /**
     * Get all available permissions.
     *
     * @return Collection<int, Permission>
     */
    #[Computed]
    public function permissions(): Collection
    {
        return Permission::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        $selectedUser = null;

        if ($this->selectedUserId !== null) {
            $selectedUser = User::query()
                ->with(['roles:id,name', 'permissions:id,name'])
                ->find($this->selectedUserId);
        }

        return view('livewire.admin.user-management', [
            'selectedUser' => $selectedUser,
        ]);
    }
}
