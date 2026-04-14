<section class="space-y-8">
    <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(6,182,212,0.22),_transparent_45%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(14,116,144,0.35),_transparent_45%)]"></div>
        <div class="pointer-events-none absolute -left-20 bottom-0 h-52 w-52 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/15"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-5">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('APK Vendor Dashboard') }}</flux:heading>
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-300">
                    {{ __('Operational landing page for procurement reporting: vendor lifecycle, RFQ pipeline, purchase activity, and invoice monitoring.') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap gap-2">
                @can('vendor.manage')
                    <flux:button variant="primary" :href="route('vendor.register')" wire:navigate>{{ __('Manage Vendors') }}</flux:button>
                @endcan
                @can('rfq.create')
                    <flux:button :href="route('rfqs.create')" wire:navigate>{{ __('Create RFQ') }}</flux:button>
                @endcan
                @can('po.create')
                    <flux:button :href="route('pos.create')" wire:navigate>{{ __('Create PO') }}</flux:button>
                @endcan
                @if ($vendor !== null)
                    <flux:button :href="route('vendor.profile')" wire:navigate>{{ __('Vendor Profile') }}</flux:button>
                @endif
            </div>
        </div>
    </div>

    @if ($isVendorPending)
        <flux:callout icon="exclamation-triangle" variant="warning">
            {{ __('Your vendor account is pending approval. Please complete your profile and supporting documents to proceed.') }}
            <flux:link class="ms-2" :href="route('vendor.profile')" wire:navigate>{{ __('Complete profile') }}</flux:link>
        </flux:callout>
    @endif

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Dashboard Summary') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Landing KPI overview') }}</flux:text>
        </div>

        @php
            $summaryGridClass = $canViewVendorSummary ? 'sm:grid-cols-2 xl:grid-cols-5' : 'sm:grid-cols-2 xl:grid-cols-3';
        @endphp

        <div class="grid gap-4 {{ $summaryGridClass }}">
            @if ($canViewVendorSummary)
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Vendor') }}</flux:text>
                    <flux:heading class="mt-2" size="xl">{{ $totalVendors }}</flux:heading>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Vendor Approved vs Pending') }}</flux:text>
                    <div class="mt-2 flex items-end gap-2">
                        <flux:heading size="xl">{{ $approvedVendorsCount }}</flux:heading>
                        <flux:text class="pb-1 text-zinc-500 dark:text-zinc-400">{{ __('approved') }}</flux:text>
                    </div>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ $pendingVendorsCount }} {{ __('pending') }}</flux:text>
                </div>
            @endif

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('RFQ Aktif') }}</flux:text>
                <flux:heading class="mt-2" size="xl">{{ $activeRfqsCount }}</flux:heading>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('PO Aktif') }}</flux:text>
                <flux:heading class="mt-2" size="xl">{{ $activePoCount }}</flux:heading>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Invoice Pending') }}</flux:text>
                <flux:heading class="mt-2" size="xl">{{ $pendingInvoicesCount }}</flux:heading>
            </div>
        </div>
    </section>

    @php
        $chartGridClass = $canViewVendorSummary ? 'xl:grid-cols-5' : 'xl:grid-cols-1';
    @endphp

    <section class="grid gap-4 {{ $chartGridClass }}">
        @if ($canViewVendorSummary)
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <flux:heading>{{ __('Vendor Status Chart') }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Approved / Pending / Rejected') }}</flux:text>
                </div>

                <div class="mt-5 flex items-center gap-6">
                    <div class="relative h-28 w-28 rounded-full" style="background: {{ $vendorStatusChart['background'] }};">
                        <div class="absolute inset-4 rounded-full bg-white dark:bg-zinc-900"></div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-green-600"></span>
                            <span>{{ __('Approved') }}: {{ $approvedVendorsCount }} ({{ $vendorStatusChart['approved_percent'] }}%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            <span>{{ __('Pending') }}: {{ $pendingVendorsCount }} ({{ $vendorStatusChart['pending_percent'] }}%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span>{{ __('Rejected') }}: {{ $totalVendors - $approvedVendorsCount - $pendingVendorsCount }} ({{ $vendorStatusChart['rejected_percent'] }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 {{ $canViewVendorSummary ? 'xl:col-span-3' : 'xl:col-span-1' }}">
            <div class="flex items-center justify-between">
                <flux:heading>{{ __('Operational Volume Chart') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Relative current workload') }}</flux:text>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($operationalBars as $bar)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">{{ __($bar['label']) }}</span>
                            <span class="text-zinc-500 dark:text-zinc-400">{{ $bar['value'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                            <div class="h-full rounded-full {{ $bar['color_class'] }}" style="width: {{ $bar['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($canViewVendorReport)
        <section class="space-y-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Vendor Report') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Vendor performance and status') }}</flux:text>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/40">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('RFQ Joined') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('PO Won') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total Transaction') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($vendorsReport as $vendorItem)
                            @php
                                $statusColor = match ($vendorItem->status->value) {
                                    'approved' => 'green',
                                    'pending' => 'amber',
                                    default => 'red',
                                };
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium">{{ $vendorItem->company_name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $vendorItem->user?->name }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <flux:badge :color="$statusColor">{{ strtoupper($vendorItem->status->value) }}</flux:badge>
                                </td>
                                <td class="px-3 py-2">{{ $vendorItem->rfqs_count }}</td>
                                <td class="px-3 py-2">{{ $vendorItem->purchase_orders_count }}</td>
                                <td class="px-3 py-2">{{ number_format((float) ($vendorItem->total_transaction_amount ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-zinc-500 dark:text-zinc-400">{{ __('No vendor data available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $vendorsReport->links() }}</div>
        </section>
    @endif

    @if ($canViewRfqReport)
        <section class="space-y-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('RFQ Report') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('RFQ activity and vendor participation') }}</flux:text>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/40">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('RFQ') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Vendor Joined') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Selected Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($rfqsReport as $rfqItem)
                            @php
                                $selectedVendor = $rfqItem->purchaseOrders->first()?->vendor?->company_name;
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium">{{ $rfqItem->title }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $rfqItem->deadline?->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $rfqItem->vendors_count }}</td>
                                <td class="px-3 py-2">{{ $selectedVendor ?? '-' }}</td>
                                <td class="px-3 py-2">
                                    <flux:badge :color="$rfqItem->status->value === 'open' ? 'green' : 'zinc'">{{ strtoupper($rfqItem->status->value) }}</flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-zinc-500 dark:text-zinc-400">{{ __('No RFQ data available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $rfqsReport->links() }}</div>
        </section>
    @endif

    @if ($canViewPurchaseReport)
        <section class="space-y-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Purchase Report (PO)') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Most-used operational purchase report') }}</flux:text>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/40">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('PO') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total Price') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($purchaseOrdersReport as $purchaseOrderItem)
                            @php
                                $purchaseStatusColor = match ($purchaseOrderItem->status->value) {
                                    'completed' => 'green',
                                    'approved' => 'cyan',
                                    default => 'amber',
                                };
                            @endphp
                            <tr>
                                <td class="px-3 py-2 font-medium">#{{ $purchaseOrderItem->id }}</td>
                                <td class="px-3 py-2">{{ $purchaseOrderItem->vendor?->company_name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $purchaseOrderItem->total_price, 2) }}</td>
                                <td class="px-3 py-2">
                                    <flux:badge :color="$purchaseStatusColor">{{ strtoupper($purchaseOrderItem->status->value) }}</flux:badge>
                                </td>
                                <td class="px-3 py-2">{{ $purchaseOrderItem->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-zinc-500 dark:text-zinc-400">{{ __('No purchase order data available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $purchaseOrdersReport->links() }}</div>
        </section>
    @endif

    @if ($canViewInvoiceReport)
        <section class="space-y-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Invoice Report') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Invoice pipeline and approval status') }}</flux:text>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/40">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Invoice') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Value') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($invoicesReport as $invoiceItem)
                            @php
                                $invoiceStatusColor = match ($invoiceItem->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'amber',
                                };
                            @endphp
                            <tr>
                                <td class="px-3 py-2 font-medium">#{{ $invoiceItem->id }}</td>
                                <td class="px-3 py-2">
                                    <flux:badge :color="$invoiceStatusColor">{{ strtoupper($invoiceItem->status->value) }}</flux:badge>
                                </td>
                                <td class="px-3 py-2">{{ $invoiceItem->vendor?->company_name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ number_format((float) ($invoiceItem->purchaseOrder?->total_price ?? 0), 2) }}</td>
                                <td class="px-3 py-2">{{ $invoiceItem->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-zinc-500 dark:text-zinc-400">{{ __('No invoice data available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $invoicesReport->links() }}</div>
        </section>
    @endif
</section>
