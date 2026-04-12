    <section class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Create RFQ') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Create a request and assign approved vendors.') }}</flux:text>
        </div>

        <form wire:submit="save" class="space-y-5 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:field>
                <flux:label>{{ __('Title') }}</flux:label>
                <flux:input wire:model="title" type="text" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="4" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Deadline') }}</flux:label>
                <flux:input wire:model="deadline" type="datetime-local" />
                <flux:error name="deadline" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Assign Vendors') }}</flux:label>
                <div class="grid gap-2 rounded-md border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-2">
                    @forelse ($this->availableVendors as $vendor)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="vendorIds" value="{{ $vendor->id }}" class="rounded border-zinc-300" />
                            <span>{{ $vendor->company_name }} ({{ $vendor->user->name }})</span>
                        </label>
                    @empty
                        <span class="text-zinc-500">{{ __('No approved vendors available.') }}</span>
                    @endforelse
                </div>
                <flux:error name="vendorIds" />
                <flux:error name="vendorIds.*" />
            </flux:field>

            <flux:button type="submit" variant="primary">{{ __('Save RFQ') }}</flux:button>
        </form>
    </section>
