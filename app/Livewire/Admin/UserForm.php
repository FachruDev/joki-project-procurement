<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('User Form')]
class UserForm extends Component
{
    private const string SUPER_ADMIN_ROLE = 'SuperAdmin';

    public ?User $user = null;

    public bool $isEdit = false;

    public bool $isLocked = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    /**
     * @var list<int>
     */
    public array $selectedRoleIds = [];

    /**
     * @var list<int>
     */
    public array $selectedPermissionIds = [];

    /**
     * Mount component state.
     */
    public function mount(?User $user = null): void
    {
        Gate::authorize('user.manage');

        if ($user === null) {
            return;
        }

        $user->loadMissing(['roles:id,name', 'permissions:id,name']);

        $this->user = $user;
        $this->isEdit = true;
        $this->isLocked = $user->hasRole(self::SUPER_ADMIN_ROLE);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoleIds = $user->roles->pluck('id')->all();
        $this->selectedPermissionIds = $user->permissions->pluck('id')->all();
    }

    /**
     * Save user create/update form.
     */
    public function save(): void
    {
        Gate::authorize('user.manage');

        $validated = $this->validate($this->rules());

        if ($this->isEdit && $this->isLocked) {
            $this->addError('name', __('SuperAdmin account is protected and cannot be modified.'));

            return;
        }

        if ($this->isEdit && $this->user !== null) {
            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (($validated['password'] ?? '') !== '') {
                $payload['password'] = $validated['password'];
            }

            $this->user->update($payload);

            $targetUser = $this->user->fresh();
        } else {
            $targetUser = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
        }

        $roleNames = Role::query()
            ->whereIn('id', $validated['selectedRoleIds'] ?? [])
            ->pluck('name')
            ->all();

        $permissionNames = Permission::query()
            ->whereIn('id', $validated['selectedPermissionIds'] ?? [])
            ->pluck('name')
            ->all();

        $targetUser->syncRoles($roleNames);
        $targetUser->syncPermissions($permissionNames);

        Flux::toast(
            variant: 'success',
            text: $this->isEdit ? __('User updated successfully.') : __('User created successfully.'),
        );

        $this->redirect(route('management.users', absolute: false), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $userId = $this->user?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $this->isEdit
                ? ['nullable', 'string', 'min:8', 'same:passwordConfirmation']
                : ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => $this->isEdit
                ? ['nullable', 'string', 'min:8', 'required_with:password']
                : ['required', 'string', 'min:8'],
            'selectedRoleIds' => ['nullable', 'array'],
            'selectedRoleIds.*' => ['integer', Rule::exists('roles', 'id')],
            'selectedPermissionIds' => ['nullable', 'array'],
            'selectedPermissionIds.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }

    /**
     * Get available roles.
     */
    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    /**
     * Get available permissions.
     */
    #[Computed]
    public function permissions()
    {
        return Permission::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.admin.user-form');
    }
}
