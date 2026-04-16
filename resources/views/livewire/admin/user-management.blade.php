<section class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('User Management') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage users in table format. Create and edit are handled in dedicated pages.') }}</flux:text>
        </div>

        <flux:button variant="primary" :href="route('management.users.create')" wire:navigate>
            {{ __('Create User') }}
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-3 md:grid-cols-4">
            <flux:field class="md:col-span-2">
                <flux:label>{{ __('Search User') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="name / email" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Rows') }}</flux:label>
                <flux:select wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </flux:select>
            </flux:field>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('User') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Roles') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Direct Permissions') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Vendor Profile') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <flux:badge size="sm">{{ $role->name }}</flux:badge>
                                @empty
                                    <span class="text-zinc-500">{{ __('No role') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->permissions->take(5) as $permission)
                                    <flux:badge size="sm" color="zinc">{{ $permission->name }}</flux:badge>
                                @empty
                                    <span class="text-zinc-500">{{ __('No direct permission') }}</span>
                                @endforelse
                            </div>
                            @if ($user->permissions->count() > 5)
                                <div class="mt-1 text-xs text-zinc-500">+{{ $user->permissions->count() - 5 }} {{ __('more') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->vendor !== null)
                                <div class="font-medium">{{ $user->vendor->company_name }}</div>
                                <div class="text-xs text-zinc-500">{{ strtoupper($user->vendor->status->value) }}</div>
                            @else
                                <span class="text-zinc-500">{{ __('No vendor profile') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <flux:button size="sm" :href="route('management.users.profile', $user)" wire:navigate>
                                    {{ __('Profile') }}
                                </flux:button>

                                <flux:button size="sm" :href="route('management.users.edit', $user)" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    :disabled="$user->hasRole('SuperAdmin') || auth()->id() === $user->id"
                                    x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete User?', text: 'User ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteUser({{ $user->id }}) } })()"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No users found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>
</section>
