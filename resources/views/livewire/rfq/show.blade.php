    <section class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $rfq->title }}</flux:heading>
                <flux:text class="mt-1">{{ $rfq->description }}</flux:text>
                <flux:text class="mt-2 text-sm">
                    {{ __('Deadline:') }} {{ $rfq->deadline->format('Y-m-d H:i') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:badge :color="$rfq->status->value === 'open' ? 'green' : 'zinc'">
                    {{ strtoupper($rfq->status->value) }}
                </flux:badge>

                @can('update', $rfq)
                    @if ($rfq->status->value === 'open')
                        <flux:button
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Close RFQ?', text: 'RFQ akan ditutup dan tidak menerima respons baru.' })) { $wire.closeRfq() } })()"
                        >
                            {{ __('Close RFQ') }}
                        </flux:button>
                    @endif
                @endcan

                @can('update', $rfq)
                    <flux:button :href="route('rfqs.edit', $rfq)" wire:navigate>
                        {{ __('Edit') }}
                    </flux:button>
                @endcan

                @can('delete', $rfq)
                    <flux:button
                        variant="danger"
                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete RFQ?', text: 'RFQ ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteRfq() } })()"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Assigned Vendors') }}</flux:heading>
            <ul class="mt-3 list-disc ps-6 text-sm">
                @foreach ($rfq->vendors as $vendor)
                    <li>{{ $vendor->company_name }} ({{ $vendor->user->name }})</li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Responses') }}</flux:heading>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Price') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($rfq->responses as $response)
                            <tr>
                                <td class="px-3 py-2">{{ $response->vendor->company_name }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $response->price, 2) }}</td>
                                <td class="px-3 py-2">{{ $response->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-zinc-500">{{ __('No responses yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('po.create')
            <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading>{{ __('Create Purchase Order') }}</flux:heading>
                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <flux:field class="min-w-64">
                        <flux:label>{{ __('Select Vendor (from responses)') }}</flux:label>
                        <flux:select wire:model="selectedVendorId">
                            <option value="">{{ __('Choose vendor') }}</option>
                            @foreach ($rfq->responses as $response)
                                <option value="{{ $response->vendor_id }}">{{ $response->vendor->company_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selectedVendorId" />
                    </flux:field>

                    <flux:button
                        variant="primary"
                        x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Create Purchase Order?', text: 'PO baru akan dibuat dari RFQ ini.' })) { $wire.createPurchaseOrder() } })()"
                    >
                        {{ __('Create PO') }}
                    </flux:button>
                </div>
            </div>
        @endcan
    </section>
