<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">
                {{ $isEdit ? __('Edit User') : __('Create User') }}
            </flux:heading>
            <flux:text class="mt-1">
                {{ $isEdit ? __('Update user profile, roles, and direct permissions.') : __('Create a new user and assign role/permission access.') }}
            </flux:text>
        </div>

        <flux:button :href="route('management.users')" wire:navigate>
            {{ __('Back to User List') }}
        </flux:button>
    </div>

    @if ($isLocked)
        <flux:callout icon="lock-closed" variant="warning">
            {{ __('SuperAdmin account is protected and cannot be modified.') }}
        </flux:callout>
    @endif

    <form
        wire:submit="save"
        class="space-y-5 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        data-swal-confirm
        data-swal-title="{{ $isEdit ? 'Update User?' : 'Create User?' }}"
        data-swal-text="{{ $isEdit ? 'Perubahan data user akan disimpan.' : 'User baru akan dibuat dengan akses yang dipilih.' }}"
        data-swal-icon="question"
    >
        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" type="text" :disabled="$isLocked" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model="email" type="email" :disabled="$isLocked" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input wire:model="password" type="password" :placeholder="$isEdit ? __('Leave empty to keep current password') : ''" :disabled="$isLocked" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirm Password') }}</flux:label>
                <flux:input wire:model="passwordConfirmation" type="password" :disabled="$isLocked" />
                <flux:error name="passwordConfirmation" />
            </flux:field>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div>
                <flux:label>{{ __('Roles') }}</flux:label>
                <div class="mt-2 grid max-h-72 gap-2 overflow-y-auto rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                    @foreach ($this->roles as $role)
                        <label class="flex items-center gap-2" wire:key="form-role-{{ $role->id }}">
                            <input type="checkbox" wire:model="selectedRoleIds" value="{{ $role->id }}" class="rounded border-zinc-300" @disabled($isLocked) />
                            <span>{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="selectedRoleIds" />
                <flux:error name="selectedRoleIds.*" />
            </div>

            <div>
                <flux:label>{{ __('Direct Permissions') }}</flux:label>
                <div class="mt-2 grid max-h-72 gap-2 overflow-y-auto rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                    @foreach ($this->permissions as $permission)
                        <label class="flex items-center gap-2" wire:key="form-permission-{{ $permission->id }}">
                            <input type="checkbox" wire:model="selectedPermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" @disabled($isLocked) />
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="selectedPermissionIds" />
                <flux:error name="selectedPermissionIds.*" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button type="button" variant="ghost" :href="route('management.users')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" :disabled="$isLocked">
                {{ $isEdit ? __('Save Changes') : __('Create User') }}
            </flux:button>
        </div>
    </form>
</section>
