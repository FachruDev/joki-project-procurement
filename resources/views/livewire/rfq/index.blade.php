    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('RFQ List') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Browse and manage Request for Quotation records.') }}</flux:text>
            </div>

            @can('rfq.create')
                <flux:button variant="primary" :href="route('rfqs.create')" wire:navigate>
                    {{ __('Create RFQ') }}
                </flux:button>
            @endcan
        </div>

        <div class="space-y-3">
            @forelse ($rfqs as $rfq)
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <flux:heading>{{ $rfq->title }}</flux:heading>
                            <flux:text class="mt-1">{{ \Illuminate\Support\Str::limit($rfq->description, 120) }}</flux:text>
                            <flux:text class="mt-2 text-sm">
                                {{ __('Deadline:') }} {{ $rfq->deadline->format('Y-m-d H:i') }}
                                | {{ __('Responses:') }} {{ $rfq->responses_count }}
                            </flux:text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:badge :color="$rfq->status->value === 'open' ? 'green' : 'zinc'">
                                {{ strtoupper($rfq->status->value) }}
                            </flux:badge>

                            <flux:button size="sm" :href="route('rfqs.show', $rfq)" wire:navigate>
                                {{ __('View') }}
                            </flux:button>

                            @can('rfq.respond')
                                <flux:button size="sm" variant="primary" :href="route('rfqs.respond', $rfq)" wire:navigate>
                                    {{ __('Respond') }}
                                </flux:button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700">
                    {{ __('No RFQs found.') }}
                </div>
            @endforelse
        </div>
    </section>
