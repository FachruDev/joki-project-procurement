<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Invoice List') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Complete invoice records with vendor and PO context.') }}</flux:text>
        </div>

        <flux:button :href="route('invoices.my')" wire:navigate>
            {{ __('Open My Invoice') }}
        </flux:button>
    </div>

    <div class="grid gap-3 rounded-lg border border-zinc-200 p-4 md:grid-cols-4 dark:border-zinc-700">
        <flux:field>
            <flux:label>{{ __('Vendor Name') }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="searchVendor" type="text" placeholder="vendor / user name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select wire:model.live="statusFilter">
                <option value="all">{{ __('All') }}</option>
                <option value="pending">{{ __('Pending') }}</option>
                <option value="approved">{{ __('Approved') }}</option>
                <option value="rejected">{{ __('Rejected') }}</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Date From') }}</flux:label>
            <flux:input wire:model.live="dateFrom" type="date" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Date To') }}</flux:label>
            <flux:input wire:model.live="dateTo" type="date" />
        </flux:field>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Invoice #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Vendor') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Value') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-4 py-3">#{{ $invoice->id }}</td>
                        <td class="px-4 py-3">#{{ $invoice->po_id }}</td>
                        <td class="px-4 py-3">{{ $invoice->vendor?->company_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2) }}</td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$invoice->status->value === 'approved' ? 'green' : ($invoice->status->value === 'rejected' ? 'red' : 'amber')">
                                {{ strtoupper($invoice->status->value) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ $invoice->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @can('vendor.manage')
                                    <flux:button size="sm" :href="route('vendor.show', $invoice->vendor)" wire:navigate>
                                        {{ __('View Vendor') }}
                                    </flux:button>
                                @endcan
                                <flux:button size="sm" :href="route('invoices.show', $invoice)" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>
                                @if ($invoice->status->value === 'approved')
                                    <flux:button size="sm" variant="filled" :href="route('invoices.print', $invoice)" target="_blank">
                                        {{ __('Print PDF') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-zinc-500">{{ __('No invoices found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $invoices->links() }}</div>
</section>
