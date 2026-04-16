<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ $isEdit ? __('Edit Role') : __('Create Role') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Create or update role names for access grouping.') }}</flux:text>
        </div>

        <flux:button :href="route('management.permissions')" wire:navigate>
            {{ __('Back to Management') }}
        </flux:button>
    </div>

    @if ($isLocked)
        <flux:callout icon="lock-closed" variant="warning">
            {{ __('Protected role names cannot be renamed.') }}
        </flux:callout>
    @endif

    <form
        wire:submit="save"
        class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        data-swal-confirm
        data-swal-title="{{ $isEdit ? 'Update Role?' : 'Create Role?' }}"
        data-swal-text="{{ $isEdit ? 'Nama role akan diperbarui.' : 'Role baru akan ditambahkan ke sistem.' }}"
        data-swal-icon="question"
    >
        <flux:field>
            <flux:label>{{ __('Role Name') }}</flux:label>
            <flux:input wire:model="roleName" type="text" placeholder="Finance" :disabled="$isLocked" />
            <flux:error name="roleName" />
        </flux:field>

        <div class="flex justify-end gap-2">
            <flux:button type="button" variant="ghost" :href="route('management.permissions')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" :disabled="$isLocked">
                {{ $isEdit ? __('Save Changes') : __('Create Role') }}
            </flux:button>
        </div>
    </form>
</section>
