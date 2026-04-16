<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Role Form')]
class RoleForm extends Component
{
    private const array PROTECTED_ROLE_NAMES = ['SuperAdmin', 'Procurement', 'Vendor'];

    public ?Role $role = null;

    public bool $isEdit = false;

    public bool $isLocked = false;

    public string $roleName = '';

    /**
     * Mount the component.
     */
    public function mount(?Role $role = null): void
    {
        Gate::authorize('permission.manage');

        if ($role === null) {
            return;
        }

        $this->role = $role;
        $this->isEdit = true;
        $this->isLocked = in_array($role->name, self::PROTECTED_ROLE_NAMES, true);
        $this->roleName = $role->name;
    }

    /**
     * Save role form.
     */
    public function save(): void
    {
        Gate::authorize('permission.manage');

        if ($this->isEdit && $this->isLocked) {
            $this->addError('roleName', __('Protected roles cannot be renamed.'));

            return;
        }

        $validated = $this->validate([
            'roleName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role?->id),
            ],
        ]);

        if ($this->isEdit && $this->role !== null) {
            $this->role->update([
                'name' => $validated['roleName'],
            ]);

            Flux::toast(variant: 'success', text: __('Role updated successfully.'));
        } else {
            Role::findOrCreate($validated['roleName'], 'web');

            Flux::toast(variant: 'success', text: __('Role created successfully.'));
        }

        $this->redirect(route('management.permissions', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.role-form');
    }
}
