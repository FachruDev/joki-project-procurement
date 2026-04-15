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
    private const string SUPER_ADMIN_ROLE = 'SuperAdmin';

    public string $search = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?int $selectedUserId = null;

    public bool $selectedUserIsLocked = false;

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
        $this->selectedUserIsLocked = $this->isSuperAdminUser($user);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->selectedRoleIds = $user->roles->pluck('id')->all();
        $this->selectedPermissionIds = $user->permissions->pluck('id')->all();
    }

    /**
     * Prepare form for creating a new user.
     */
    public function prepareCreateUser(): void
    {
        Gate::authorize('user.manage');

        $this->reset([
            'selectedUserId',
            'selectedUserIsLocked',
            'name',
            'email',
            'password',
            'passwordConfirmation',
            'selectedRoleIds',
            'selectedPermissionIds',
        ]);
    }

    /**
     * Create a new user with role and permission assignments.
     */
    public function createUser(): void
    {
        Gate::authorize('user.manage');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string', 'min:8'],
            'selectedRoleIds' => ['nullable', 'array'],
            'selectedRoleIds.*' => ['integer', Rule::exists('roles', 'id')],
            'selectedPermissionIds' => ['nullable', 'array'],
            'selectedPermissionIds.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

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

        $this->selectUser($user->id);

        Flux::toast(variant: 'success', text: __('User created successfully.'));
    }

    /**
     * Update selected user profile data.
     */
    public function updateUser(): void
    {
        Gate::authorize('user.manage');

        $validated = $this->validate([
            'selectedUserId' => ['required', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->selectedUserId)],
            'password' => ['nullable', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['nullable', 'string', 'min:8'],
        ]);

        $user = User::query()->findOrFail($validated['selectedUserId']);

        if ($this->isSuperAdminUser($user)) {
            $this->addError('selectedUserId', __('SuperAdmin account is protected and cannot be modified.'));

            return;
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (($validated['password'] ?? '') !== '') {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        Flux::toast(variant: 'success', text: __('User updated successfully.'));
    }

    /**
     * Delete selected user.
     */
    public function deleteUser(int $userId): void
    {
        Gate::authorize('user.manage');

        $user = User::query()->findOrFail($userId);

        if ($this->isSuperAdminUser($user)) {
            Flux::toast(variant: 'danger', text: __('SuperAdmin account cannot be deleted.'));

            return;
        }

        if ($user->id === auth()->id()) {
            Flux::toast(variant: 'danger', text: __('You cannot delete your own account.'));

            return;
        }

        $user->delete();

        if ($this->selectedUserId === $userId) {
            $this->prepareCreateUser();
        }

        Flux::toast(variant: 'success', text: __('User deleted successfully.'));
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

        if ($this->isSuperAdminUser($user)) {
            $this->addError('selectedUserId', __('SuperAdmin account is protected and cannot be modified.'));

            return;
        }

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
            'selectedUserIsLocked' => $this->selectedUserIsLocked,
        ]);
    }

    private function isSuperAdminUser(User $user): bool
    {
        return $user->hasRole(self::SUPER_ADMIN_ROLE);
    }
}
