<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('My RFQ') }}</flux:heading>
            <flux:text class="mt-1">{{ __('RFQ records created by you or assigned to your vendor account.') }}</flux:text>
        </div>

        @can('rfq.create')
            <flux:button variant="primary" :href="route('rfqs.create')" wire:navigate>
                {{ __('Create RFQ') }}
            </flux:button>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('RFQ') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Deadline') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Responses') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($rfqs as $rfq)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $rfq->title }}</div>
                            <div class="text-xs text-zinc-500">{{ \Illuminate\Support\Str::limit($rfq->description, 80) }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $rfq->deadline->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $rfq->responses_count }}</td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$rfq->status->value === 'open' ? 'green' : 'zinc'">
                                {{ strtoupper($rfq->status->value) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" :href="route('rfqs.show', $rfq)" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>

                                @can('rfq.respond')
                                    @if ($rfq->status->value === 'open')
                                        <flux:button size="sm" variant="primary" :href="route('rfqs.respond', $rfq)" wire:navigate>
                                            {{ __('Respond') }}
                                        </flux:button>
                                    @endif
                                @endcan

                                @can('update', $rfq)
                                    <flux:button size="sm" :href="route('rfqs.edit', $rfq)" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:button>
                                @endcan

                                @can('delete', $rfq)
                                    @if ($rfq->status->value === 'open')
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete RFQ?', text: 'RFQ ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteRfq({{ $rfq->id }}) } })()"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No RFQs available.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
