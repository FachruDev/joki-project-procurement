<section class="space-y-6">
    <div class="relative overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-cyan-100 via-white to-blue-100 p-6 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-cyan-300/30 blur-3xl dark:bg-cyan-500/20"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-indigo-300/25 blur-3xl dark:bg-indigo-500/20"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('System Comparison Report') }}</flux:heading>
                <flux:text class="mt-1 max-w-3xl text-zinc-700 dark:text-zinc-300">
                    {{ __('Perbandingan menyeluruh data Vendor, RFQ, Purchase Order, dan Invoice untuk membantu monitoring kinerja operasional procurement.') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($canExportReport)
                    <flux:button
                        icon="arrow-down-tray"
                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Export Report?', text: 'Laporan sistem akan diunduh dalam format Excel.', icon: 'question', confirmButtonText: 'Ya, export' })) { $wire.exportToExcel() } })()"
                    >
                        {{ __('Export Excel') }}
                    </flux:button>
                @endif

                <flux:button :href="route('dashboard')" wire:navigate>
                    {{ __('Back to Dashboard') }}
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Vendor') }}</flux:text>
                <flux:icon icon="users" class="size-5 text-cyan-600 dark:text-cyan-400" />
            </div>
            <flux:heading class="mt-2" size="xl">{{ $totalVendor }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total RFQ') }}</flux:text>
                <flux:icon icon="document-text" class="size-5 text-indigo-600 dark:text-indigo-400" />
            </div>
            <flux:heading class="mt-2" size="xl">{{ $totalRfq }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Purchase Order') }}</flux:text>
                <flux:icon icon="shopping-cart" class="size-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <flux:heading class="mt-2" size="xl">{{ $totalPo }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Invoice') }}</flux:text>
                <flux:icon icon="receipt-percent" class="size-5 text-amber-600 dark:text-amber-400" />
            </div>
            <flux:heading class="mt-2" size="xl">{{ $totalInvoice }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Vendor Status') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Doughnut') }}</flux:text>
            </div>
            <div class="mt-4 h-64">
                <canvas id="vendor-status-chart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Operational Snapshot') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Bar') }}</flux:text>
            </div>
            <div class="mt-4 h-64">
                <canvas id="operational-snapshot-chart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Monthly Trend') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Line') }}</flux:text>
            </div>
            <div class="mt-4 h-64">
                <canvas id="monthly-trend-chart" class="h-full w-full"></canvas>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('PO Status & Value Comparison') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Volume dan nilai per status') }}</flux:text>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Count') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total Value') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($purchaseOrderStatus as $status => $meta)
                            <tr>
                                <td class="px-3 py-2">{{ strtoupper($status) }}</td>
                                <td class="px-3 py-2">{{ $meta['count'] }}</td>
                                <td class="px-3 py-2">{{ number_format($meta['value'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Top Vendors by Transaction') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Ranking berdasarkan total nilai PO') }}</flux:text>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('RFQ Joined') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('PO Count') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total Transaction') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($topVendors as $vendor)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium">{{ $vendor->company_name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $vendor->user?->name ?? '-' }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $vendor->rfqs_count }}</td>
                                <td class="px-3 py-2">{{ $vendor->purchase_orders_count }}</td>
                                <td class="px-3 py-2">{{ number_format((float) ($vendor->total_transaction_amount ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-zinc-500">{{ __('No vendor transaction data available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @endonce

    <script>
        (() => {
            window.__reportChartPayload = @js($chartData);
            window.__reportChartInstances = window.__reportChartInstances || [];

            const renderCharts = () => {
                if (typeof window.Chart === 'undefined') {
                    return;
                }

                window.__reportChartInstances.forEach((chartInstance) => chartInstance.destroy());
                window.__reportChartInstances = [];

                const payload = window.__reportChartPayload;
                if (!payload) {
                    return;
                }

                const darkMode = document.documentElement.classList.contains('dark');
                const labelColor = darkMode ? '#d4d4d8' : '#3f3f46';
                const gridColor = darkMode ? 'rgba(82, 82, 91, 0.45)' : 'rgba(212, 212, 216, 0.7)';

                const vendorStatusChart = document.getElementById('vendor-status-chart');
                if (vendorStatusChart) {
                    window.__reportChartInstances.push(new Chart(vendorStatusChart, {
                        type: 'doughnut',
                        data: {
                            labels: payload.vendorStatus.labels,
                            datasets: [{
                                data: payload.vendorStatus.data,
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderColor: darkMode ? '#18181b' : '#ffffff',
                                borderWidth: 3,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: labelColor },
                                },
                            },
                        },
                    }));
                }

                const operationalSnapshotChart = document.getElementById('operational-snapshot-chart');
                if (operationalSnapshotChart) {
                    window.__reportChartInstances.push(new Chart(operationalSnapshotChart, {
                        type: 'bar',
                        data: {
                            labels: payload.operationalSnapshot.labels,
                            datasets: [{
                                label: 'Total',
                                data: payload.operationalSnapshot.data,
                                backgroundColor: ['#06b6d4', '#6366f1', '#f59e0b'],
                                borderRadius: 8,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: { color: labelColor },
                                    grid: { color: gridColor },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: labelColor, precision: 0 },
                                    grid: { color: gridColor },
                                },
                            },
                            plugins: {
                                legend: { display: false },
                            },
                        },
                    }));
                }

                const monthlyTrendChart = document.getElementById('monthly-trend-chart');
                if (monthlyTrendChart) {
                    window.__reportChartInstances.push(new Chart(monthlyTrendChart, {
                        type: 'line',
                        data: {
                            labels: payload.monthlyTrend.labels,
                            datasets: [
                                {
                                    label: 'Vendor',
                                    data: payload.monthlyTrend.vendors,
                                    borderColor: '#06b6d4',
                                    backgroundColor: 'rgba(6, 182, 212, 0.2)',
                                    fill: false,
                                    tension: 0.35,
                                },
                                {
                                    label: 'RFQ',
                                    data: payload.monthlyTrend.rfqs,
                                    borderColor: '#6366f1',
                                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                                    fill: false,
                                    tension: 0.35,
                                },
                                {
                                    label: 'PO',
                                    data: payload.monthlyTrend.pos,
                                    borderColor: '#22c55e',
                                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                    fill: false,
                                    tension: 0.35,
                                },
                                {
                                    label: 'Invoice',
                                    data: payload.monthlyTrend.invoices,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                                    fill: false,
                                    tension: 0.35,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: { color: labelColor },
                                    grid: { color: gridColor },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: labelColor, precision: 0 },
                                    grid: { color: gridColor },
                                },
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: labelColor },
                                },
                            },
                        },
                    }));
                }
            };

            if (!window.__reportChartEventsBound) {
                window.__reportChartEventsBound = true;
                document.addEventListener('livewire:navigated', renderCharts);
                document.addEventListener('DOMContentLoaded', renderCharts);
            }

            renderCharts();
        })();
    </script>
</section>
