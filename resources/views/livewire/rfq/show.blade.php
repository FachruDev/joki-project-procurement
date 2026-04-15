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
                    @if ($rfq->status->value === 'open')
                        <flux:button
                            variant="danger"
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete RFQ?', text: 'RFQ ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteRfq() } })()"
                        >
                            {{ __('Delete') }}
                        </flux:button>
                    @endif
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
                            <th class="px-3 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($rfq->responses as $response)
                            <tr>
                                <td class="px-3 py-2">{{ $response->vendor->company_name }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $response->price, 2) }}</td>
                                <td class="px-3 py-2">{{ $response->notes ?: '-' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <flux:button size="sm" wire:click="openResponseHistory({{ $response->id }})">
                                        {{ __('View History') }}
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-zinc-500">{{ __('No responses yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($showResponseHistoryModal)
            <div class="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6">
                <button type="button" class="absolute inset-0 bg-black/55" wire:click="closeResponseHistory" aria-label="{{ __('Close') }}"></button>

                <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                        <div>
                            <flux:heading size="lg">{{ __('Response History') }}</flux:heading>
                            @if ($selectedResponse !== null)
                                <flux:text class="mt-1">
                                    {{ $selectedResponse->vendor->company_name }} | {{ __('Current Price:') }} {{ number_format((float) $selectedResponse->price, 2) }}
                                </flux:text>
                            @endif
                        </div>

                        <flux:button size="sm" variant="ghost" wire:click="closeResponseHistory">
                            {{ __('Close') }}
                        </flux:button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-5">
                        <div class="space-y-4">
                            @forelse ($responseHistoryLogs as $history)
                                @php
                                    $attributes = $history->properties['attributes'] ?? [];
                                    $oldValues = $history->properties['old'] ?? [];
                                    $changedKeys = array_values(array_unique(array_merge(array_keys($attributes), array_keys($oldValues))));
                                @endphp
                                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ strtoupper(str_replace('rfq_response_', '', $history->description)) }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $history->created_at?->format('Y-m-d H:i:s') }} | {{ $history->causer?->name ?? __('System') }}
                                        </div>
                                    </div>

                                    @if ($changedKeys !== [])
                                        <div class="mt-3 overflow-x-auto">
                                            <table class="min-w-full divide-y divide-zinc-200 text-xs dark:divide-zinc-700">
                                                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">{{ __('Field') }}</th>
                                                        <th class="px-3 py-2 text-left">{{ __('Old') }}</th>
                                                        <th class="px-3 py-2 text-left">{{ __('New') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                                    @foreach ($changedKeys as $field)
                                                        <tr>
                                                            <td class="px-3 py-2">{{ $field }}</td>
                                                            <td class="px-3 py-2">{{ (string) ($oldValues[$field] ?? '-') }}</td>
                                                            <td class="px-3 py-2">{{ (string) ($attributes[$field] ?? '-') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <flux:text class="mt-3 text-zinc-500">{{ __('No detailed field changes captured for this event.') }}</flux:text>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-lg border border-zinc-200 p-5 text-center text-zinc-500 dark:border-zinc-700">
                                    {{ __('No activity history found for this response.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
