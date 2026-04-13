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
    public string $permissionName = '';

    public string $roleName = '';

    public ?int $selectedRoleId = null;

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
     * Select role to manage permissions.
     */
    public function selectRole(int $roleId): void
    {
        Gate::authorize('permission.manage');

        $role = Role::query()
            ->with('permissions:id,name')
            ->findOrFail($roleId);

        $this->selectedRoleId = $role->id;
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
        return view('livewire.admin.permission-management');
    }
}
