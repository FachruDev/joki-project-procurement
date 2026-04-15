<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('My PO') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Purchase orders assigned to your vendor or created by your procurement account.') }}</flux:text>
        </div>

        @can('po.create')
            <flux:button variant="primary" :href="route('pos.create')" wire:navigate>
                {{ __('Create PO') }}
            </flux:button>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Vendor') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('RFQ') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Total') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3">#{{ $order->id }}</td>
                        <td class="px-4 py-3">{{ $order->vendor->company_name }}</td>
                        <td class="px-4 py-3">{{ $order->rfq?->title ?? '-' }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $order->total_price, 2) }}</td>
                        <td class="px-4 py-3">
                            <flux:badge>{{ strtoupper($order->status->value) }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" :href="route('pos.show', $order)" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>

                                @can('update', $order)
                                    <flux:button size="sm" :href="route('pos.edit', $order)" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:button>
                                @endcan

                                @can('delete', $order)
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete PO?', text: 'Purchase order ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deletePurchaseOrder({{ $order->id }}) } })()"
                                    >
                                        {{ __('Delete') }}
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('No purchase orders found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
