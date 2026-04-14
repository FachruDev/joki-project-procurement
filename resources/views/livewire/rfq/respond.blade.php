    <section class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Submit RFQ Response') }}</flux:heading>
            <flux:text class="mt-1">{{ $rfq->title }}</flux:text>
        </div>

        @if ($alreadySubmitted)
            <flux:callout icon="check-circle" variant="success">
                {{ __('You have already submitted a response for this RFQ.') }}
            </flux:callout>
        @endif

        <form
            wire:submit="submit"
            class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
            data-swal-confirm
            data-swal-title="Kirim Respons RFQ?"
            data-swal-text="Respons RFQ hanya bisa dikirim satu kali."
            data-swal-icon="question"
        >
            <flux:field>
                <flux:label>{{ __('Price') }}</flux:label>
                <flux:input wire:model="price" type="number" step="0.01" min="0" />
                <flux:error name="price" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="4" />
                <flux:error name="notes" />
            </flux:field>

            <flux:button type="submit" variant="primary" :disabled="$alreadySubmitted">
                {{ __('Submit Response') }}
            </flux:button>
        </form>
    </section>
