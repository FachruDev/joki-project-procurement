<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('User Profile') }}</flux:heading>
            <flux:text class="mt-1">{{ $user->name }} ({{ $user->email }})</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button :href="route('management.users.edit', $user)" wire:navigate>
                {{ __('Edit User') }}
            </flux:button>
            <flux:button :href="route('management.users')" wire:navigate>
                {{ __('Back to User List') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-2">
            <flux:heading>{{ __('General Information') }}</flux:heading>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Name') }}</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Email') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Created') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->created_at?->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Email Verified') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->email_verified_at ? __('Yes') : __('No') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading>{{ __('Activity Summary') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">{{ __('Created RFQ') }}</span>
                    <span class="font-semibold">{{ $user->created_rfqs_count }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">{{ __('Created PO') }}</span>
                    <span class="font-semibold">{{ $user->created_purchase_orders_count }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">{{ __('Vendor Profile') }}</span>
                    <span class="font-semibold">{{ $user->vendor ? __('Available') : __('No') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading>{{ __('Roles') }}</flux:heading>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <flux:badge>{{ $role->name }}</flux:badge>
                @empty
                    <span class="text-zinc-500">{{ __('No role assigned.') }}</span>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading>{{ __('Direct Permissions') }}</flux:heading>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($user->permissions as $permission)
                    <flux:badge color="zinc">{{ $permission->name }}</flux:badge>
                @empty
                    <span class="text-zinc-500">{{ __('No direct permission assigned.') }}</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading>{{ __('Vendor Profile Detail') }}</flux:heading>

        @if ($user->vendor)
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Company') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->vendor->company_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 text-sm">{{ strtoupper($user->vendor->status->value) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Phone') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->vendor->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Address') }}</dt>
                    <dd class="mt-1 text-sm">{{ $user->vendor->address ?: '-' }}</dd>
                </div>
            </dl>
        @else
            <flux:text class="mt-4 text-zinc-500">{{ __('This user does not have a vendor profile.') }}</flux:text>
        @endif
    </div>
</section>
