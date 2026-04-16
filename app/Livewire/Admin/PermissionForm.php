<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Title('Permission Form')]
class PermissionForm extends Component
{
    public ?Permission $permission = null;

    public bool $isEdit = false;

    public string $permissionName = '';

    /**
     * Mount the component.
     */
    public function mount(?Permission $permission = null): void
    {
        Gate::authorize('permission.manage');

        if ($permission === null) {
            return;
        }

        $this->permission = $permission;
        $this->isEdit = true;
        $this->permissionName = $permission->name;
    }

    /**
     * Save permission form.
     */
    public function save(): void
    {
        Gate::authorize('permission.manage');

        $validated = $this->validate([
            'permissionName' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\.\-]+$/',
                Rule::unique('permissions', 'name')->ignore($this->permission?->id),
            ],
        ]);

        if ($this->isEdit && $this->permission !== null) {
            $this->permission->update([
                'name' => $validated['permissionName'],
            ]);

            Flux::toast(variant: 'success', text: __('Permission updated successfully.'));
        } else {
            Permission::findOrCreate($validated['permissionName'], 'web');

            Flux::toast(variant: 'success', text: __('Permission created successfully.'));
        }

        $this->redirect(route('management.permissions', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.permission-form');
    }
}
