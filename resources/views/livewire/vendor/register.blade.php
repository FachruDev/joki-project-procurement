<x-layouts::app :title="__('Vendor Registration Review')">
    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Vendor Registration Review') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Review vendor profiles and approve registrations.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Status Filter') }}</flux:label>
                <flux:select wire:model.live="statusFilter" class="w-48">
                    <option value="all">{{ __('All') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="approved">{{ __('Approved') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </flux:select>
            </flux:field>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Company') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Contact') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Docs') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->vendors as $vendor)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $vendor->company_name }}</div>
                                <div class="text-zinc-500">{{ $vendor->address }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $vendor->user->name }}</div>
                                <div class="text-zinc-500">{{ $vendor->user->email }}</div>
                                <div class="text-zinc-500">{{ $vendor->phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge :color="$vendor->status->value === 'approved' ? 'green' : ($vendor->status->value === 'rejected' ? 'red' : 'amber')">
                                    {{ ucfirst($vendor->status->value) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">{{ $vendor->documents_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('vendor.approve')
                                        <flux:button size="sm" variant="primary" wire:click="approve({{ $vendor->id }})">
                                            {{ __('Approve') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="danger" wire:click="reject({{ $vendor->id }})">
                                            {{ __('Reject') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No vendors found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts::app>
