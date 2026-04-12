<x-layouts::app :title="__('Dashboard')">
    <section class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Procurement Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Track vendor approvals, RFQs, purchase orders, and invoices.') }}</flux:text>
        </div>

        @if ($isVendorPending)
            <flux:callout icon="exclamation-triangle" variant="warning">
                {{ __('Your vendor account is pending approval. You can still update your profile and upload required documents.') }}
                <flux:link class="ms-2" :href="route('vendor.profile')" wire:navigate>{{ __('Complete profile') }}</flux:link>
            </flux:callout>
        @endif

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Open RFQs') }}</flux:text>
                <flux:heading size="xl" class="mt-2">{{ $openRfqsCount }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Purchase Orders') }}</flux:text>
                <flux:heading size="xl" class="mt-2">{{ $purchaseOrdersCount }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Pending Invoices') }}</flux:text>
                <flux:heading size="xl" class="mt-2">{{ $pendingInvoicesCount }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Pending Vendor Approvals') }}</flux:text>
                <flux:heading size="xl" class="mt-2">{{ $pendingVendorApprovals }}</flux:heading>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @can('vendor.manage')
                <flux:button :href="route('vendor.register')" wire:navigate>{{ __('Manage Vendors') }}</flux:button>
            @endcan
            @can('rfq.view')
                <flux:button :href="route('rfqs.index')" wire:navigate>{{ __('View RFQs') }}</flux:button>
            @endcan
            @can('po.view')
                <flux:button :href="route('pos.index')" wire:navigate>{{ __('View Purchase Orders') }}</flux:button>
            @endcan
            @can('invoice.approve')
                <flux:button :href="route('invoices.approve')" wire:navigate>{{ __('Approve Invoices') }}</flux:button>
            @endcan
            @if ($vendor !== null)
                <flux:button :href="route('vendor.profile')" wire:navigate>{{ __('Vendor Profile') }}</flux:button>
            @endif
        </div>
    </section>
</x-layouts::app>
