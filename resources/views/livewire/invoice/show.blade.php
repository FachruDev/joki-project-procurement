<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Invoice Detail') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Invoice #') }}{{ $invoice->id }} | {{ __('PO #') }}{{ $invoice->po_id }}</flux:text>
        </div>

        <div class="flex gap-2">
            @if ($canPrintPdf)
                <flux:button variant="filled" :href="route('invoices.print', $invoice)" target="_blank">{{ __('Print PDF') }}</flux:button>
            @endif
            @can('invoice.approve')
                <flux:button :href="route('invoices.approve')" wire:navigate>{{ __('Back to Invoice List') }}</flux:button>
            @else
                <flux:button :href="route('invoices.my')" wire:navigate>{{ __('Back to My Invoice') }}</flux:button>
            @endcan
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
            <flux:heading>{{ __('Invoice Information') }}</flux:heading>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Status') }}</dt>
                    <dd class="mt-1">
                        <flux:badge :color="$invoice->status->value === 'approved' ? 'green' : ($invoice->status->value === 'pending' ? 'amber' : 'red')">
                            {{ strtoupper($invoice->status->value) }}
                        </flux:badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Vendor') }}</dt>
                    <dd class="mt-1 text-sm">{{ $invoice->vendor?->company_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Contact') }}</dt>
                    <dd class="mt-1 text-sm">{{ $invoice->vendor?->user?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Invoice Date') }}</dt>
                    <dd class="mt-1 text-sm">{{ $invoice->created_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('PO Total Value') }}</dt>
                    <dd class="mt-1 text-sm">{{ number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading>{{ __('Invoice File') }}</flux:heading>
            <div class="mt-4">
                @php
                    $invoiceMedia = $invoice->getFirstMedia('invoice-files');
                @endphp
                @if ($invoiceMedia !== null)
                    <flux:button size="sm" :href="route('media.show', $invoiceMedia)" target="_blank">
                        {{ __('View Uploaded File') }}
                    </flux:button>
                @else
                    <flux:text class="text-zinc-500">{{ __('Invoice file is missing.') }}</flux:text>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading>{{ __('Purchase Order Items') }}</flux:heading>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Item') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Qty') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Price') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse (($invoice->purchaseOrder?->items ?? collect()) as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->item_name }}</td>
                            <td class="px-4 py-3">{{ $item->qty }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $item->price, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $item->qty * (float) $item->price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-zinc-500">{{ __('No PO items available.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
