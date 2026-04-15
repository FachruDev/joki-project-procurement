<section class="space-y-6">
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('System Comparison Report') }}</flux:heading>
                <flux:text class="mt-1 max-w-3xl text-zinc-600 dark:text-zinc-300">
                    {{ __('Perbandingan menyeluruh data Vendor, RFQ, Purchase Order, dan Invoice untuk membantu monitoring kinerja operasional procurement.') }}
                </flux:text>
            </div>

            <flux:button :href="route('dashboard')" wire:navigate>
                {{ __('Back to Dashboard') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Vendor') }}</flux:text>
            <flux:heading class="mt-2" size="xl">{{ $totalVendor }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total RFQ') }}</flux:text>
            <flux:heading class="mt-2" size="xl">{{ $totalRfq }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Purchase Order') }}</flux:text>
            <flux:heading class="mt-2" size="xl">{{ $totalPo }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Invoice') }}</flux:text>
            <flux:heading class="mt-2" size="xl">{{ $totalInvoice }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Vendor Status Comparison') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Approved vs Pending vs Rejected') }}</flux:text>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <tr>
                            <td class="px-3 py-2">{{ __('Approved') }}</td>
                            <td class="px-3 py-2">{{ $vendorStatusCounts['approved'] }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">{{ __('Pending') }}</td>
                            <td class="px-3 py-2">{{ $vendorStatusCounts['pending'] }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">{{ __('Rejected') }}</td>
                            <td class="px-3 py-2">{{ $vendorStatusCounts['rejected'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('RFQ Status Comparison') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Open vs Closed') }}</flux:text>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <tr>
                            <td class="px-3 py-2">{{ __('Open') }}</td>
                            <td class="px-3 py-2">{{ $rfqStatusCounts['open'] }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">{{ __('Closed') }}</td>
                            <td class="px-3 py-2">{{ $rfqStatusCounts['closed'] }}</td>
                        </tr>
                    </tbody>
                </table>
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
                <flux:heading size="lg">{{ __('Invoice Status Comparison') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Pending vs Approved vs Rejected') }}</flux:text>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <tr>
                            <td class="px-3 py-2">{{ __('Pending') }}</td>
                            <td class="px-3 py-2">{{ $invoiceStatusCounts['pending'] }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">{{ __('Approved') }}</td>
                            <td class="px-3 py-2">{{ $invoiceStatusCounts['approved'] }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">{{ __('Rejected') }}</td>
                            <td class="px-3 py-2">{{ $invoiceStatusCounts['rejected'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Monthly Data Comparison (Last 6 Months)') }}</flux:heading>
            <flux:text class="text-xs text-zinc-500">{{ __('Vendor / RFQ / PO / Invoice') }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Month') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('RFQ') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('PO') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Invoice') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($monthlyComparisons as $monthly)
                        <tr>
                            <td class="px-3 py-2">{{ $monthly['label'] }}</td>
                            <td class="px-3 py-2">{{ $monthly['vendors'] }}</td>
                            <td class="px-3 py-2">{{ $monthly['rfqs'] }}</td>
                            <td class="px-3 py-2">{{ $monthly['pos'] }}</td>
                            <td class="px-3 py-2">{{ $monthly['invoices'] }}</td>
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
</section>
