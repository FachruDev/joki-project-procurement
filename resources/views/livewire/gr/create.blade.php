    <section class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Record Goods Receipt') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Purchase Order #') }}{{ $purchaseOrder->id }}</flux:text>
        </div>

        <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:field>
                <flux:label>{{ __('Received Date') }}</flux:label>
                <flux:input wire:model="receivedDate" type="datetime-local" />
                <flux:error name="receivedDate" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="4" />
                <flux:error name="notes" />
            </flux:field>

            <flux:button type="submit" variant="primary">{{ __('Save Goods Receipt') }}</flux:button>
        </form>
    </section>
