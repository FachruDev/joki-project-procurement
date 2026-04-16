<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ $isEdit ? __('Edit Permission') : __('Create Permission') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Use dot notation, for example: report.view') }}</flux:text>
        </div>

        <flux:button :href="route('management.permissions')" wire:navigate>
            {{ __('Back to Management') }}
        </flux:button>
    </div>

    <form
        wire:submit="save"
        class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        data-swal-confirm
        data-swal-title="{{ $isEdit ? 'Update Permission?' : 'Create Permission?' }}"
        data-swal-text="{{ $isEdit ? 'Nama permission akan diperbarui.' : 'Permission baru akan ditambahkan ke sistem.' }}"
        data-swal-icon="question"
    >
        <flux:field>
            <flux:label>{{ __('Permission Name') }}</flux:label>
            <flux:input wire:model="permissionName" type="text" placeholder="report.view" />
            <flux:error name="permissionName" />
        </flux:field>

        <div class="flex justify-end gap-2">
            <flux:button type="button" variant="ghost" :href="route('management.permissions')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ $isEdit ? __('Save Changes') : __('Create Permission') }}
            </flux:button>
        </div>
    </form>
</section>
