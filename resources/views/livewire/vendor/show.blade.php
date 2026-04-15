<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Vendor Detail') }}</flux:heading>
            <flux:text class="mt-1">{{ $vendor->company_name }}</flux:text>
        </div>

        @can('vendor.manage')
            <flux:button :href="route('vendor.index')" wire:navigate>{{ __('Back to Vendor List') }}</flux:button>
        @else
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Back to Dashboard') }}</flux:button>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
            <flux:heading>{{ __('Vendor Profile') }}</flux:heading>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Company Name') }}</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $vendor->company_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Status') }}</dt>
                    <dd class="mt-1">
                        <flux:badge :color="$vendor->status->value === 'approved' ? 'green' : ($vendor->status->value === 'pending' ? 'amber' : 'red')">
                            {{ strtoupper($vendor->status->value) }}
                        </flux:badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Contact Person') }}</dt>
                    <dd class="mt-1 text-sm">{{ $vendor->user?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Email') }}</dt>
                    <dd class="mt-1 text-sm">{{ $vendor->user?->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Phone') }}</dt>
                    <dd class="mt-1 text-sm">{{ $vendor->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Address') }}</dt>
                    <dd class="mt-1 text-sm">{{ $vendor->address ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading>{{ __('Summary') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">{{ __('Documents') }}</span>
                    <span class="font-semibold">{{ $vendor->documents->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">{{ __('Approved Invoices') }}</span>
                    <span class="font-semibold">{{ $approvedInvoices->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading>{{ __('Vendor Documents') }}</flux:heading>
        <div class="mt-4 space-y-3">
            @forelse ($vendor->documents as $document)
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <div>
                        <div class="font-medium">{{ $document->document_type }}</div>
                        <div class="text-xs text-zinc-500">{{ __('Uploaded at:') }} {{ $document->created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                    @php
                        $documentMedia = $document->getFirstMedia('documents');
                    @endphp
                    @if ($documentMedia !== null)
                        <flux:button size="sm" :href="route('media.show', $documentMedia)" target="_blank">
                            {{ __('View File') }}
                        </flux:button>
                    @else
                        <span class="text-sm text-zinc-500">{{ __('No file') }}</span>
                    @endif
                </div>
            @empty
                <flux:text class="text-zinc-500">{{ __('No documents uploaded yet.') }}</flux:text>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading>{{ __('Approved Invoices') }}</flux:heading>
        <div class="mt-4 overflow-x-auto">
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
                    @forelse ($approvedInvoices as $invoice)
                        <tr>
                            <td class="px-4 py-3">#{{ $invoice->id }}</td>
                            <td class="px-4 py-3">#{{ $invoice->po_id }}</td>
                            <td class="px-4 py-3">{{ number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $invoiceMedia = $invoice->getFirstMedia('invoice-files');
                                @endphp
                                @if ($invoiceMedia !== null)
                                    <a href="{{ route('media.show', $invoiceMedia) }}" target="_blank" class="text-blue-600 underline dark:text-blue-400">{{ __('Open') }}</a>
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
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No approved invoices for this vendor.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
