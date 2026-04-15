<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('My Invoice') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Invoice activity scoped to your vendor or procurement account.') }}</flux:text>
        </div>

        <flux:button :href="route('invoices.list')" wire:navigate>
            {{ __('Open Invoice List') }}
        </flux:button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Vendor') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('PO Total') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Invoice Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('File') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($purchaseOrders as $purchaseOrder)
                    @php
                        $invoice = $purchaseOrder->invoice;
                    @endphp
                    <tr>
                        <td class="px-4 py-3">#{{ $purchaseOrder->id }}</td>
                        <td class="px-4 py-3">{{ $purchaseOrder->vendor?->company_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $purchaseOrder->total_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($invoice)
                                <flux:badge :color="$invoice->status->value === 'approved' ? 'green' : ($invoice->status->value === 'rejected' ? 'red' : 'amber')">
                                    {{ strtoupper($invoice->status->value) }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc">{{ __('NOT UPLOADED') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $invoiceMedia = $invoice?->getFirstMedia('invoice-files');
                            @endphp
                            @if ($invoiceMedia !== null)
                                <a href="{{ route('media.show', $invoiceMedia) }}" target="_blank" class="text-blue-600 underline dark:text-blue-400">
                                    {{ __('Open') }}
                                </a>
                            @else
                                <span class="text-zinc-500">{{ __('Missing') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($invoice)
                                    <flux:button size="sm" :href="route('invoices.show', $invoice)" wire:navigate>
                                        {{ __('View') }}
                                    </flux:button>
                                    @if ($invoice->status->value === 'approved')
                                        <flux:button size="sm" variant="filled" :href="route('invoices.print', $invoice)" target="_blank">
                                            {{ __('Print PDF') }}
                                        </flux:button>
                                    @endif
                                @endif

                                @if ($canUploadInvoice && $purchaseOrder->vendor_id === auth()->user()->vendor?->id)
                                    <flux:button size="sm" :href="route('invoices.upload', $purchaseOrder)" wire:navigate>
                                        {{ $invoice ? __('Update Invoice') : __('Upload Invoice') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('No invoice-related purchase orders found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
