<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Role & Permission Management') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Create roles/permissions and configure role permission mapping.') }}</flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Create Permission') }}</flux:heading>
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
                    <flux:input wire:model="permissionName" type="text" placeholder="example: user.manage" />
                    <flux:error name="permissionName" />
                </flux:field>

                <flux:button type="submit">{{ __('Create Permission') }}</flux:button>
            </form>

            <flux:separator />

            <flux:heading>{{ __('Create Role') }}</flux:heading>
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
                    <flux:input wire:model="roleName" type="text" placeholder="example: Supervisor" />
                    <flux:error name="roleName" />
                </flux:field>

                <flux:button type="submit">{{ __('Create Role') }}</flux:button>
            </form>
        </div>

        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Role Permission Mapping') }}</flux:heading>

            <flux:field>
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
                @if ($selectedRoleIsLocked)
                    <flux:callout icon="lock-closed" variant="warning" class="mb-4">
                        {{ __('SuperAdmin role is protected and cannot be edited.') }}
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
                                <input type="checkbox" wire:model="rolePermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" @disabled($selectedRoleIsLocked) />
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <flux:error name="rolePermissionIds" />
                    <flux:error name="rolePermissionIds.*" />

                    <flux:button type="submit" variant="primary" :disabled="$selectedRoleIsLocked">{{ __('Save Mapping') }}</flux:button>
                </form>
            @endif
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
        <flux:heading>{{ __('Current Role Matrix') }}</flux:heading>
        <div class="mt-4 space-y-3">
            @foreach ($this->roles as $role)
                <div class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="font-medium">
                        {{ $role->name }}
                        @if ($role->name === 'SuperAdmin')
                            <flux:badge size="sm" color="purple" class="ms-1">{{ __('Protected') }}</flux:badge>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @forelse ($role->permissions as $permission)
                            <flux:badge size="sm">{{ $permission->name }}</flux:badge>
                        @empty
                            <span class="text-sm text-zinc-500">{{ __('No permissions assigned') }}</span>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
