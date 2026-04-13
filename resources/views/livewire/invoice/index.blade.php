<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('My Invoice Upload') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Select purchase orders and upload or update invoice files.') }}</flux:text>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('PO Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Total') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Invoice Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Invoice File') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3">#{{ $order->id }}</td>
                        <td class="px-4 py-3">
                            <flux:badge>{{ strtoupper($order->status->value) }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ number_format((float) $order->total_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($order->invoice)
                                <flux:badge :color="$order->invoice->status->value === 'approved' ? 'green' : ($order->invoice->status->value === 'rejected' ? 'red' : 'amber')">
                                    {{ strtoupper($order->invoice->status->value) }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc">{{ __('NOT UPLOADED') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($order->invoice && $order->invoice->getFirstMediaUrl('invoice-files') !== '')
                                <a href="{{ $order->invoice->getFirstMediaUrl('invoice-files') }}" target="_blank" class="text-blue-600 underline dark:text-blue-400">
                                    {{ __('Open') }}
                                </a>
                            @else
                                <span class="text-zinc-500">{{ __('Missing') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" :href="route('invoices.upload', $order)" wire:navigate>
                                {{ $order->invoice ? __('Update Invoice') : __('Upload Invoice') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('No purchase orders found for your vendor account.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
