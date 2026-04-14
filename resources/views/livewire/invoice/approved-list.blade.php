<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Approved Invoices') }}</flux:heading>
        <flux:text class="mt-1">{{ __('List of approved invoices from your submitted procurement transactions.') }}</flux:text>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Invoice #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Value') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('File') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-4 py-3">#{{ $invoice->id }}</td>
                        <td class="px-4 py-3">#{{ $invoice->po_id }}</td>
                        <td class="px-4 py-3">{{ number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($invoice->getFirstMediaUrl('invoice-files') !== '')
                                <a href="{{ $invoice->getFirstMediaUrl('invoice-files') }}" target="_blank" class="text-blue-600 underline dark:text-blue-400">
                                    {{ __('Open') }}
                                </a>
                            @else
                                <span class="text-zinc-500">{{ __('Missing') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" :href="route('invoices.show', $invoice)" wire:navigate>{{ __('View') }}</flux:button>
                                <flux:button size="sm" variant="filled" :href="route('invoices.print', $invoice)" target="_blank">{{ __('Print PDF') }}</flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No approved invoices found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
