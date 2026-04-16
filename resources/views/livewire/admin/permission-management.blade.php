<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Role & Permission Management') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Manage roles and permissions in table layout with dedicated create/edit pages.') }}</flux:text>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <flux:field class="w-full">
                    <flux:label>{{ __('Search Role') }}</flux:label>
                    <flux:input wire:model.live.debounce.300ms="roleSearch" type="text" placeholder="role name" />
                </flux:field>

                <flux:button class="mt-6" :href="route('management.roles.create')" wire:navigate>
                    {{ __('Create Role') }}
                </flux:button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Role') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Permissions') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Users') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->roles as $role)
                            @php
                                $isProtectedRole = in_array($role->name, ['SuperAdmin', 'Procurement', 'Vendor'], true);
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $role->name }}</span>
                                        @if ($isProtectedRole)
                                            <flux:badge size="sm" color="purple">{{ __('Protected Name') }}</flux:badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2">{{ $role->permissions_count }}</td>
                                <td class="px-3 py-2">{{ $role->users_count }}</td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="subtle" wire:click="selectRole({{ $role->id }})">
                                            {{ __('Mapping') }}
                                        </flux:button>
                                        <flux:button size="sm" :href="route('management.roles.edit', $role)" wire:navigate :disabled="$isProtectedRole">
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            :disabled="$isProtectedRole"
                                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete Role?', text: 'Role ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteRole({{ $role->id }}) } })()"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-5 text-center text-zinc-500">{{ __('No roles found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <flux:field class="w-full">
                    <flux:label>{{ __('Search Permission') }}</flux:label>
                    <flux:input wire:model.live.debounce.300ms="permissionSearch" type="text" placeholder="permission name" />
                </flux:field>

                <flux:button class="mt-6" :href="route('management.permissions.create')" wire:navigate>
                    {{ __('Create Permission') }}
                </flux:button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Permission') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Roles') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Users') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->permissions as $permission)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $permission->name }}</td>
                                <td class="px-3 py-2">{{ $permission->roles_count }}</td>
                                <td class="px-3 py-2">{{ $permission->users_count }}</td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" :href="route('management.permissions.edit', $permission)" wire:navigate>
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete Permission?', text: 'Permission ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deletePermission({{ $permission->id }}) } })()"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-5 text-center text-zinc-500">{{ __('No permissions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between">
            <flux:heading>{{ __('Role Permission Mapping') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500">{{ __('Select a role from table and update its permission mapping.') }}</flux:text>
        </div>

        @if ($selectedRoleId !== null)
            @if ($selectedRoleNameLocked)
                <flux:callout icon="information-circle" variant="secondary" class="my-4">
                    {{ __('Role name is protected. You can still change permission mapping.') }}
                </flux:callout>
            @endif

            <form
                wire:submit="saveRolePermissions"
                class="space-y-4"
                data-swal-confirm
                data-swal-title="Simpan Mapping Role?"
                data-swal-text="Perubahan permission pada role ini akan disimpan."
                data-swal-icon="question"
            >
                <div class="grid max-h-80 gap-2 overflow-y-auto rounded-md border border-zinc-200 p-3 dark:border-zinc-700 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($this->permissions as $permission)
                        <label class="flex items-center gap-2" wire:key="mapping-permission-{{ $permission->id }}">
                            <input type="checkbox" wire:model="rolePermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" />
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>

                <flux:error name="selectedRoleId" />
                <flux:error name="rolePermissionIds" />
                <flux:error name="rolePermissionIds.*" />

                <flux:button type="submit" variant="primary">{{ __('Save Mapping') }}</flux:button>
            </form>
        @else
            <div class="mt-4 rounded-lg border border-dashed border-zinc-300 p-5 text-center text-sm text-zinc-500 dark:border-zinc-700">
                {{ __('No role selected. Click "Mapping" action in role table to start.') }}
            </div>
        @endif
    </div>
</section>
