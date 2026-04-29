<section class="space-y-8">
    <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(6,182,212,0.22),_transparent_45%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(14,116,144,0.35),_transparent_45%)]"></div>
        <div class="pointer-events-none absolute -left-20 bottom-0 h-52 w-52 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/15"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-5">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('APK Vendor Dashboard') }}</flux:heading>
                <flux:text class="max-w-3xl text-zinc-600 dark:text-zinc-300">
                    {{ __('Dashboard fokus pada monitoring operasional saat ini dan tindakan cepat. Analitik komparasi periode, tren historis, dan laporan lintas modul tersedia penuh di menu Report.') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($canViewSystemReport)
                    <flux:button variant="primary" icon="presentation-chart-line" :href="route('reports.index')" wire:navigate>{{ __('Open Full Reports') }}</flux:button>
                @endif
                @can('vendor.manage')
                    <flux:button :href="route('vendor.register')" wire:navigate>{{ __('Manage Vendors') }}</flux:button>
                @endcan
                @can('rfq.create')
                    <flux:button :href="route('rfqs.my')" wire:navigate>{{ __('Create RFQ') }}</flux:button>
                @endcan
                @can('po.create')
                    <flux:button :href="route('pos.my')" wire:navigate>{{ __('Create PO') }}</flux:button>
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
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Operational KPI snapshot') }}</flux:text>
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

    <section class="space-y-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ __('Operational Feed') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Realtime list of the latest transactions and workflow activity.') }}</flux:text>
            </div>
            @if ($canViewSystemReport)
                <flux:link :href="route('reports.index')" wire:navigate>{{ __('Need deeper comparison? Open Reports') }}</flux:link>
            @endif
        </div>

        @php
            $feedGridColumns = 0;
            $feedGridColumns += $canViewVendorSummary ? 1 : 0;
            $feedGridColumns += $canViewRfqReport ? 1 : 0;
            $feedGridColumns += $canViewPurchaseReport ? 1 : 0;
            $feedGridColumns += $canViewInvoiceReport ? 1 : 0;
            $feedGridClass = match ($feedGridColumns) {
                4 => 'xl:grid-cols-4',
                3 => 'xl:grid-cols-3',
                2 => 'xl:grid-cols-2',
                default => 'xl:grid-cols-1',
            };
        @endphp

        <div class="grid gap-4 {{ $feedGridClass }}">
            @if ($canViewVendorSummary)
                <div class="rounded-xl border border-zinc-200/90 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Latest Vendor Updates') }}</flux:heading>
                        @can('vendor.manage')
                            <flux:link :href="route('vendor.index')" wire:navigate>{{ __('View all') }}</flux:link>
                        @endcan
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentVendorStatuses as $vendorItem)
                            @php
                                $statusColor = match ($vendorItem->status->value) {
                                    'approved' => 'green',
                                    'pending' => 'amber',
                                    default => 'red',
                                };
                            @endphp
                            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                <div class="font-medium">{{ $vendorItem->company_name }}</div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $vendorItem->user?->name ?? '-' }}</span>
                                    <flux:badge :color="$statusColor">{{ strtoupper($vendorItem->status->value) }}</flux:badge>
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-sm text-zinc-500">{{ __('No vendor updates.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($canViewRfqReport)
                <div class="rounded-xl border border-zinc-200/90 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Latest RFQ') }}</flux:heading>
                        <flux:link :href="route('rfqs.my')" wire:navigate>{{ __('View all') }}</flux:link>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentRfqs as $rfqItem)
                            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                <div class="line-clamp-1 font-medium">{{ $rfqItem->title }}</div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}: {{ $rfqItem->vendors_count }}</span>
                                    <flux:badge :color="$rfqItem->status->value === 'open' ? 'green' : 'zinc'">{{ strtoupper($rfqItem->status->value) }}</flux:badge>
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-sm text-zinc-500">{{ __('No RFQ activity.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($canViewPurchaseReport)
                <div class="rounded-xl border border-zinc-200/90 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Latest Purchase Order') }}</flux:heading>
                        <flux:link :href="route('pos.my')" wire:navigate>{{ __('View all') }}</flux:link>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentPurchaseOrders as $purchaseOrderItem)
                            @php
                                $purchaseStatusColor = match ($purchaseOrderItem->status->value) {
                                    'completed' => 'green',
                                    'approved' => 'cyan',
                                    default => 'amber',
                                };
                            @endphp
                            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                <div class="font-medium">#{{ $purchaseOrderItem->id }} · {{ $purchaseOrderItem->vendor?->company_name ?? '-' }}</div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format((float) $purchaseOrderItem->total_price, 2) }}</span>
                                    <flux:badge :color="$purchaseStatusColor">{{ strtoupper($purchaseOrderItem->status->value) }}</flux:badge>
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-sm text-zinc-500">{{ __('No PO activity.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($canViewInvoiceReport)
                <div class="rounded-xl border border-zinc-200/90 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Latest Invoice') }}</flux:heading>
                        <flux:link :href="route('invoices.my')" wire:navigate>{{ __('View all') }}</flux:link>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentInvoices as $invoiceItem)
                            @php
                                $invoiceStatusColor = match ($invoiceItem->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'amber',
                                };
                            @endphp
                            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                <div class="font-medium">#{{ $invoiceItem->id }} · {{ $invoiceItem->vendor?->company_name ?? '-' }}</div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format((float) ($invoiceItem->purchaseOrder?->total_price ?? 0), 2) }}</span>
                                    <flux:badge :color="$invoiceStatusColor">{{ strtoupper($invoiceItem->status->value) }}</flux:badge>
                                </div>
                            </div>
                        @empty
                            <flux:text class="text-sm text-zinc-500">{{ __('No invoice activity.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </section>
</section>
