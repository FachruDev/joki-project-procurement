    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Invoice Approval') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Review and approve/reject uploaded invoices.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Status Filter') }}</flux:label>
                <flux:select wire:model.live="statusFilter" class="w-48">
                    <option value="all">{{ __('All') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="approved">{{ __('Approved') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </flux:select>
                <flux:error name="statusFilter" />
            </flux:field>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Invoice #') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Vendor') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('File') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-4 py-3">#{{ $invoice->id }}</td>
                            <td class="px-4 py-3">#{{ $invoice->po_id }}</td>
                            <td class="px-4 py-3">{{ $invoice->vendor->company_name }}</td>
                            <td class="px-4 py-3">
                                <flux:badge :color="$invoice->status->value === 'approved' ? 'green' : ($invoice->status->value === 'rejected' ? 'red' : 'amber')">
                                    {{ strtoupper($invoice->status->value) }}
                                </flux:badge>
                            </td>
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
                                    <flux:button
                                        size="sm"
                                        variant="primary"
                                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Approve Invoice', text: 'Status invoice akan diubah menjadi approved.' })) { $wire.approve({{ $invoice->id }}) } })()"
                                    >
                                        {{ __('Approve') }}
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Reject Invoice', text: 'Status invoice akan diubah menjadi rejected.', confirmButtonText: 'Ya, reject' })) { $wire.reject({{ $invoice->id }}) } })()"
                                    >
                                        {{ __('Reject') }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('No invoices found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
