<?php

namespace App\Livewire\Admin;

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

#[Title('Role & Permission Management')]
class PermissionManagement extends Component
{
    private const array PROTECTED_ROLE_NAMES = ['SuperAdmin', 'Procurement', 'Vendor'];

    public string $roleSearch = '';

    public string $permissionSearch = '';

    public ?int $selectedRoleId = null;

    public bool $selectedRoleNameLocked = false;

    /**
     * @var list<int>
     */
    public array $rolePermissionIds = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('permission.manage');
    }

    /**
     * Select role to manage permission mapping.
     */
    public function selectRole(int $roleId): void
    {
        Gate::authorize('permission.manage');

        $role = Role::query()
            ->with('permissions:id,name')
            ->findOrFail($roleId);

        $this->selectedRoleId = $role->id;
        $this->selectedRoleNameLocked = $this->isProtectedRoleName($role->name);
        $this->rolePermissionIds = $role->permissions->pluck('id')->all();
    }

    /**
     * Delete selected permission.
     */
    public function deletePermission(int $permissionId): void
    {
        Gate::authorize('permission.manage');

        $permission = Permission::query()->findOrFail($permissionId);
        $permission->delete();

        if ($this->selectedRoleId !== null) {
            $role = Role::query()->find($this->selectedRoleId);

            if ($role !== null) {
                $this->selectRole($role->id);
            }
        }

        Flux::toast(variant: 'success', text: __('Permission deleted successfully.'));
    }

    /**
     * Delete selected role.
     */
    public function deleteRole(int $roleId): void
    {
        Gate::authorize('permission.manage');

        $role = Role::query()->findOrFail($roleId);

        if ($this->isProtectedRoleName($role->name)) {
            Flux::toast(variant: 'danger', text: __('Protected roles cannot be deleted.'));

            return;
        }

        $role->delete();

        if ($this->selectedRoleId === $roleId) {
            $this->reset('selectedRoleId', 'selectedRoleNameLocked', 'rolePermissionIds');
        }

        Flux::toast(variant: 'success', text: __('Role deleted successfully.'));
    }

    /**
     * Save role-permission mapping.
     */
    public function saveRolePermissions(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'selectedRoleId' => ['required', 'integer', Rule::exists('roles', 'id')],
            'rolePermissionIds' => ['nullable', 'array'],
            'rolePermissionIds.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $role = Role::query()->findOrFail($validated['selectedRoleId']);

        $permissionNames = Permission::query()
            ->whereIn('id', $validated['rolePermissionIds'] ?? [])
            ->pluck('name')
            ->all();

        $role->syncPermissions($permissionNames);

        Flux::toast(variant: 'success', text: __('Role permissions updated.'));
    }

    /**
     * Get roles list.
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::query()
            ->withCount(['permissions', 'users'])
            ->when(
                $this->roleSearch !== '',
                fn ($query) => $query->where('name', 'like', '%'.$this->roleSearch.'%'),
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Get permission list.
     *
     * @return Collection<int, Permission>
     */
    #[Computed]
    public function permissions(): Collection
    {
        return Permission::query()
            ->withCount(['roles', 'users'])
            ->when(
                $this->permissionSearch !== '',
                fn ($query) => $query->where('name', 'like', '%'.$this->permissionSearch.'%'),
            )
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.permission-management');
    }

    private function isProtectedRoleName(string $roleName): bool
    {
        return in_array($roleName, self::PROTECTED_ROLE_NAMES, true);
    }
}
