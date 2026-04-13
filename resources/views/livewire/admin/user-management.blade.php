<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('User Management') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Manage user roles and direct permissions using Spatie Permission.') }}</flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:field>
                <flux:label>{{ __('Search User') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="name / email" />
            </flux:field>

            <div class="max-h-[32rem] space-y-2 overflow-y-auto">
                @forelse ($this->users as $user)
                    <button
                        type="button"
                        wire:click="selectUser({{ $user->id }})"
                        class="w-full rounded-lg border px-3 py-3 text-start transition hover:border-zinc-400 dark:hover:border-zinc-500 {{ $selectedUser?->id === $user->id ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/30' : 'border-zinc-200 dark:border-zinc-700' }}"
                    >
                        <div class="font-medium">{{ $user->name }}</div>
                        <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($user->roles as $role)
                                <flux:badge size="sm">{{ $role->name }}</flux:badge>
                            @endforeach
                        </div>
                    </button>
                @empty
                    <div class="rounded-md border border-dashed border-zinc-300 p-5 text-center text-sm text-zinc-500 dark:border-zinc-700">
                        {{ __('No users found.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            @if ($selectedUser)
                <div class="mb-4">
                    <flux:heading>{{ __('Assign Access') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ $selectedUser->name }} ({{ $selectedUser->email }})</flux:text>
                </div>

                <form wire:submit="saveAssignments" class="space-y-5">
                    <div>
                        <flux:label>{{ __('Roles') }}</flux:label>
                        <div class="mt-2 grid gap-2 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                            @foreach ($this->roles as $role)
                                <label class="flex items-center gap-2" wire:key="role-{{ $role->id }}">
                                    <input type="checkbox" wire:model="selectedRoleIds" value="{{ $role->id }}" class="rounded border-zinc-300" />
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
                                    <input type="checkbox" wire:model="selectedPermissionIds" value="{{ $permission->id }}" class="rounded border-zinc-300" />
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="selectedPermissionIds" />
                        <flux:error name="selectedPermissionIds.*" />
                    </div>

                    <flux:button type="submit" variant="primary">{{ __('Save Access') }}</flux:button>
                </form>
            @else
                <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                    {{ __('Select a user from the left panel to manage access.') }}
                </div>
            @endif
        </div>
    </div>
</section>
