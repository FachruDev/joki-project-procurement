<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Role & Permission Management') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Full CRUD for roles and permissions with protected system role names.') }}</flux:text>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Permission CRUD') }}</flux:heading>

            <form
                wire:submit="createPermission"
                class="space-y-3"
                data-swal-confirm
                data-swal-title="Create Permission?"
                data-swal-text="Permission baru akan ditambahkan ke sistem."
                data-swal-icon="question"
            >
                <flux:field>
                    <flux:label>{{ __('Permission Name') }}</flux:label>
                    <flux:input wire:model="permissionName" type="text" placeholder="example: invoice.view" />
                    <flux:error name="permissionName" />
                </flux:field>

                <flux:button type="submit">{{ __('Create Permission') }}</flux:button>
            </form>

            <form
                wire:submit="updatePermission"
                class="space-y-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700"
            >
                <flux:field>
                    <flux:label>{{ __('Edit Permission') }}</flux:label>
                    <flux:input wire:model="editingPermissionName" type="text" placeholder="Select permission below" :disabled="$editingPermissionId === null" />
                    <flux:error name="editingPermissionId" />
                    <flux:error name="editingPermissionName" />
                </flux:field>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" :disabled="$editingPermissionId === null">{{ __('Save Permission') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelEditingPermission" :disabled="$editingPermissionId === null">{{ __('Cancel') }}</flux:button>
                </div>
            </form>

            <div class="max-h-72 space-y-2 overflow-y-auto">
                @foreach ($this->permissions as $permission)
                    <div class="flex items-center justify-between rounded-md border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <span class="font-mono text-sm">{{ $permission->name }}</span>
                        <div class="flex gap-2">
                            <flux:button size="sm" wire:click="startEditingPermission({{ $permission->id }})">{{ __('Edit') }}</flux:button>
                            <flux:button
                                size="sm"
                                variant="danger"
                                x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete Permission?', text: 'Permission ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deletePermission({{ $permission->id }}) } })()"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Role CRUD') }}</flux:heading>

            <form
                wire:submit="createRole"
                class="space-y-3"
                data-swal-confirm
                data-swal-title="Create Role?"
                data-swal-text="Role baru akan ditambahkan ke sistem."
                data-swal-icon="question"
            >
                <flux:field>
                    <flux:label>{{ __('Role Name') }}</flux:label>
                    <flux:input wire:model="roleName" type="text" placeholder="example: Finance" />
                    <flux:error name="roleName" />
                </flux:field>

                <flux:button type="submit">{{ __('Create Role') }}</flux:button>
            </form>

            <form
                wire:submit="updateRole"
                class="space-y-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700"
            >
                <flux:field>
                    <flux:label>{{ __('Edit Role Name') }}</flux:label>
                    <flux:input wire:model="editingRoleName" type="text" placeholder="Select role below" :disabled="$editingRoleId === null" />
                    <flux:error name="editingRoleId" />
                    <flux:error name="editingRoleName" />
                </flux:field>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" :disabled="$editingRoleId === null">{{ __('Save Role') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelEditingRole" :disabled="$editingRoleId === null">{{ __('Cancel') }}</flux:button>
                </div>
            </form>

            <div class="max-h-72 space-y-2 overflow-y-auto">
                @foreach ($this->roles as $role)
                    @php
                        $isProtectedRole = in_array($role->name, ['SuperAdmin', 'Procurement', 'Vendor'], true);
                    @endphp
                    <div class="flex items-center justify-between rounded-md border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span>{{ $role->name }}</span>
                            @if ($isProtectedRole)
                                <flux:badge size="sm" color="purple">{{ __('Protected Name') }}</flux:badge>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" wire:click="startEditingRole({{ $role->id }})" :disabled="$isProtectedRole">{{ __('Edit Name') }}</flux:button>
                            <flux:button
                                size="sm"
                                variant="danger"
                                :disabled="$isProtectedRole"
                                x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete Role?', text: 'Role ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteRole({{ $role->id }}) } })()"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
        <flux:heading>{{ __('Role Permission Mapping') }}</flux:heading>

        <flux:field class="mt-4">
            <flux:label>{{ __('Select Role') }}</flux:label>
            <flux:select wire:model.live="selectedRoleId" wire:change="selectRole($event.target.value)">
                <option value="">{{ __('Choose role') }}</option>
                @foreach ($this->roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </flux:select>
            <flux:error name="selectedRoleId" />
        </flux:field>

        @if ($selectedRoleId)
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
                <div class="grid max-h-72 gap-2 overflow-y-auto rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                    @foreach ($this->permissions as $permission)
                        <label class="flex items-center gap-2" wire:key="map-permission-{{ $permission->id }}">
                            <input type="checkbox" wire:model="rolePermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" />
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>

                <flux:error name="rolePermissionIds" />
                <flux:error name="rolePermissionIds.*" />

                <flux:button type="submit" variant="primary">{{ __('Save Mapping') }}</flux:button>
            </form>
        @endif
    </div>
</section>
