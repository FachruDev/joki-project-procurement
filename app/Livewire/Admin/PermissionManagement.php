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

    public string $permissionName = '';

    public string $roleName = '';

    public ?int $editingPermissionId = null;

    public string $editingPermissionName = '';

    public ?int $editingRoleId = null;

    public string $editingRoleName = '';

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
     * Create a new permission.
     */
    public function createPermission(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'permissionName' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\.\-]+$/', Rule::unique('permissions', 'name')],
        ]);

        Permission::findOrCreate($validated['permissionName'], 'web');

        $this->reset('permissionName');

        Flux::toast(variant: 'success', text: __('Permission created successfully.'));
    }

    /**
     * Create a new role.
     */
    public function createRole(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'roleName' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
        ]);

        Role::findOrCreate($validated['roleName'], 'web');

        $this->reset('roleName');

        Flux::toast(variant: 'success', text: __('Role created successfully.'));
    }

    /**
     * Start editing a permission record.
     */
    public function startEditingPermission(int $permissionId): void
    {
        Gate::authorize('permission.manage');

        $permission = Permission::query()->findOrFail($permissionId);

        $this->editingPermissionId = $permission->id;
        $this->editingPermissionName = $permission->name;
    }

    /**
     * Cancel permission edit state.
     */
    public function cancelEditingPermission(): void
    {
        $this->reset('editingPermissionId', 'editingPermissionName');
    }

    /**
     * Update selected permission.
     */
    public function updatePermission(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'editingPermissionId' => ['required', 'integer', Rule::exists('permissions', 'id')],
            'editingPermissionName' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\.\-]+$/', Rule::unique('permissions', 'name')->ignore($this->editingPermissionId)],
        ]);

        $permission = Permission::query()->findOrFail($validated['editingPermissionId']);
        $permission->update(['name' => $validated['editingPermissionName']]);

        $this->cancelEditingPermission();

        Flux::toast(variant: 'success', text: __('Permission updated successfully.'));
    }

    /**
     * Delete selected permission.
     */
    public function deletePermission(int $permissionId): void
    {
        Gate::authorize('permission.manage');

        $permission = Permission::query()->findOrFail($permissionId);
        $permission->delete();

        if ($this->editingPermissionId === $permissionId) {
            $this->cancelEditingPermission();
        }

        if ($this->selectedRoleId !== null) {
            $this->selectRole($this->selectedRoleId);
        }

        Flux::toast(variant: 'success', text: __('Permission deleted successfully.'));
    }

    /**
     * Start editing a role record.
     */
    public function startEditingRole(int $roleId): void
    {
        Gate::authorize('permission.manage');

        $role = Role::query()->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->editingRoleName = $role->name;
    }

    /**
     * Cancel role edit state.
     */
    public function cancelEditingRole(): void
    {
        $this->reset('editingRoleId', 'editingRoleName');
    }

    /**
     * Update selected role name.
     */
    public function updateRole(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'editingRoleId' => ['required', 'integer', Rule::exists('roles', 'id')],
            'editingRoleName' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingRoleId)],
        ]);

        $role = Role::query()->findOrFail($validated['editingRoleId']);

        if ($this->isProtectedRoleName($role->name)) {
            $this->addError('editingRoleName', __('Protected roles cannot be renamed.'));

            return;
        }

        $role->update([
            'name' => $validated['editingRoleName'],
        ]);

        if ($this->selectedRoleId === $role->id) {
            $this->selectRole($role->id);
        }

        $this->cancelEditingRole();

        Flux::toast(variant: 'success', text: __('Role updated successfully.'));
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

        if ($this->editingRoleId === $roleId) {
            $this->cancelEditingRole();
        }

        if ($this->selectedRoleId === $roleId) {
            $this->reset('selectedRoleId', 'selectedRoleNameLocked', 'rolePermissionIds');
        }

        Flux::toast(variant: 'success', text: __('Role deleted successfully.'));
    }

    /**
     * Select role to manage permissions.
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
     * Get all roles.
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all permissions.
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
        return view('livewire.admin.permission-management', [
            'selectedRoleNameLocked' => $this->selectedRoleNameLocked,
        ]);
    }

    private function isProtectedRoleName(string $roleName): bool
    {
        return in_array($roleName, self::PROTECTED_ROLE_NAMES, true);
    }
}
