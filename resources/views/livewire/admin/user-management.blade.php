<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('User Management') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Full CRUD user data with role and permission assignment.') }}</flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-2">
                <flux:field class="w-full">
                    <flux:label>{{ __('Search User') }}</flux:label>
                    <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="name / email" />
                </flux:field>

                <flux:button class="mt-6" wire:click="prepareCreateUser">
                    {{ __('New User') }}
                </flux:button>
            </div>

            <div class="max-h-[32rem] space-y-2 overflow-y-auto">
                @forelse ($this->users as $user)
                    <div class="rounded-lg border px-3 py-3 dark:border-zinc-700 {{ $selectedUser?->id === $user->id ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/30' : 'border-zinc-200' }}">
                        <button
                            type="button"
                            wire:click="selectUser({{ $user->id }})"
                            class="w-full text-start"
                        >
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($user->roles as $role)
                                    <flux:badge size="sm">{{ $role->name }}</flux:badge>
                                @endforeach
                            </div>
                        </button>

                        <div class="mt-3 flex justify-end">
                            <flux:button
                                size="sm"
                                variant="danger"
                                :disabled="$user->hasRole('SuperAdmin') || auth()->id() === $user->id"
                                x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete User?', text: 'User ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteUser({{ $user->id }}) } })()"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-md border border-dashed border-zinc-300 p-5 text-center text-sm text-zinc-500 dark:border-zinc-700">
                        {{ __('No users found.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-5 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <div>
                <flux:heading>{{ $selectedUser ? __('Edit User') : __('Create User') }}</flux:heading>
                @if ($selectedUser)
                    <flux:text class="mt-1 text-sm">{{ $selectedUser->name }} ({{ $selectedUser->email }})</flux:text>
                @else
                    <flux:text class="mt-1 text-sm">{{ __('Fill form then create user account.') }}</flux:text>
                @endif
            </div>

            @if ($selectedUserIsLocked)
                <flux:callout icon="lock-closed" variant="warning">
                    {{ __('SuperAdmin account is protected and cannot be modified.') }}
                </flux:callout>
            @endif

            <div class="space-y-4 rounded-md border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" />
                    <flux:error name="email" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Password') }}</flux:label>
                        <flux:input wire:model="password" type="password" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Confirm Password') }}</flux:label>
                        <flux:input wire:model="passwordConfirmation" type="password" />
                        <flux:error name="passwordConfirmation" />
                    </flux:field>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($selectedUser)
                        <flux:button
                            type="button"
                            :disabled="$selectedUserIsLocked"
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Update User?', text: 'Data user akan diperbarui.' })) { $wire.updateUser() } })()"
                        >
                            {{ __('Update User') }}
                        </flux:button>
                    @else
                        <flux:button
                            type="button"
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Create User?', text: 'User baru akan dibuat.' })) { $wire.createUser() } })()"
                        >
                            {{ __('Create User') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            <form
                wire:submit="saveAssignments"
                class="space-y-5"
                data-swal-confirm
                data-swal-title="Simpan Akses User?"
                data-swal-text="Role dan permission user ini akan diperbarui."
                data-swal-icon="question"
            >
                <div>
                    <flux:label>{{ __('Roles') }}</flux:label>
                    <div class="mt-2 grid gap-2 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                        @foreach ($this->roles as $role)
                            <label class="flex items-center gap-2" wire:key="role-{{ $role->id }}">
                                <input type="checkbox" wire:model="selectedRoleIds" value="{{ $role->id }}" class="rounded border-zinc-300" @disabled($selectedUserIsLocked) />
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="selectedRoleIds" />
                    <flux:error name="selectedRoleIds.*" />
                </div>

                <div>
                    <flux:label>{{ __('Direct Permissions') }}</flux:label>
                    <div class="mt-2 grid max-h-64 gap-2 overflow-y-auto rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                        @foreach ($this->permissions as $permission)
                            <label class="flex items-center gap-2" wire:key="permission-{{ $permission->id }}">
                                <input type="checkbox" wire:model="selectedPermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" @disabled($selectedUserIsLocked) />
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="selectedPermissionIds" />
                    <flux:error name="selectedPermissionIds.*" />
                </div>

                <flux:button type="submit" variant="primary" :disabled="$selectedUser === null || $selectedUserIsLocked">
                    {{ __('Save Access') }}
                </flux:button>
            </form>
        </div>
    </div>
</section>
