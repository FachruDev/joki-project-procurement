    <section class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Purchase Order #') }}{{ $purchaseOrder->id }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Vendor:') }} {{ $purchaseOrder->vendor->company_name }} |
                    {{ __('Status:') }} {{ strtoupper($purchaseOrder->status->value) }}
                </flux:text>
            </div>

            <div class="flex flex-wrap gap-2">
                @can('update', $purchaseOrder)
                    <flux:button :href="route('pos.edit', $purchaseOrder)" wire:navigate>
                        {{ __('Edit') }}
                    </flux:button>
                @endcan

                @can('delete', $purchaseOrder)
                    <flux:button
                        variant="danger"
                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete PO?', text: 'Purchase order ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deletePurchaseOrder() } })()"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Items') }}</flux:heading>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Item') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Qty') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Price') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-3 py-2">{{ $item->item_name }}</td>
                                <td class="px-3 py-2">{{ $item->qty }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $item->price, 2) }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $item->qty * (float) $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <flux:text class="mt-4 font-semibold">
                {{ __('Total:') }} {{ number_format((float) $purchaseOrder->total_price, 2) }}
            </flux:text>
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Goods Receipt') }}</flux:heading>
            @if ($purchaseOrder->delivery)
                <flux:text class="mt-2">
                    {{ __('Received Date:') }} {{ $purchaseOrder->delivery->received_date->format('Y-m-d H:i') }}
                </flux:text>
                <flux:text>{{ __('Notes:') }} {{ $purchaseOrder->delivery->notes ?: '-' }}</flux:text>
            @else
                <flux:text class="mt-2">{{ __('No goods receipt recorded yet.') }}</flux:text>
                @can('gr.create')
                    <flux:button class="mt-3" :href="route('gr.create', $purchaseOrder)" wire:navigate>
                        {{ __('Record GR') }}
                    </flux:button>
                @endcan
            @endif
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Invoice') }}</flux:heading>
            @if ($purchaseOrder->invoice)
                <flux:text class="mt-2">
                    {{ __('Status:') }} {{ strtoupper($purchaseOrder->invoice->status->value) }}
                </flux:text>
                @php
                    $invoiceMedia = $purchaseOrder->invoice->getFirstMedia('invoice-files');
                @endphp
                @if ($invoiceMedia !== null)
                    <a href="{{ route('media.show', $invoiceMedia) }}" target="_blank" class="mt-2 inline-block text-blue-600 underline dark:text-blue-400">
                        {{ __('View Invoice File') }}
                    </a>
                @endif
            @else
                <flux:text class="mt-2">{{ __('No invoice uploaded yet.') }}</flux:text>
            @endif

            @can('invoice.upload')
                <flux:button class="mt-3" :href="route('invoices.upload', $purchaseOrder)" wire:navigate>
                    {{ __('Upload Invoice') }}
                </flux:button>
            @endcan
        </div>
    </section>
