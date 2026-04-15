<section class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Edit Purchase Order') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Update PO vendor, RFQ relation, and line items.') }}</flux:text>
    </div>

    <form
        wire:submit="save"
        class="space-y-5 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
        data-swal-confirm
        data-swal-title="Simpan perubahan PO?"
        data-swal-text="Perubahan purchase order akan disimpan."
        data-swal-icon="question"
    >
        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('RFQ (Optional)') }}</flux:label>
                <flux:select wire:model.live="rfqId">
                    <option value="">{{ __('No RFQ') }}</option>
                    @foreach ($this->availableRfqs as $rfq)
                        <option value="{{ $rfq->id }}">#{{ $rfq->id }} - {{ $rfq->title }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="rfqId" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Vendor') }}</flux:label>
                <flux:select wire:model="vendorId">
                    <option value="">{{ __('Choose vendor') }}</option>
                    @foreach ($this->availableVendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->company_name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="vendorId" />
            </flux:field>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:heading>{{ __('Items') }}</flux:heading>
                <flux:button type="button" wire:click="addItem">{{ __('Add Item') }}</flux:button>
            </div>

            @foreach ($items as $index => $item)
                <div class="grid gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-12" wire:key="item-{{ $index }}">
                    <div class="md:col-span-6">
                        <flux:input wire:model="items.{{ $index }}.item_name" :label="__('Item Name')" type="text" />
                        <flux:error name="items.{{ $index }}.item_name" />
                    </div>
                    <div class="md:col-span-2">
                        <flux:input wire:model="items.{{ $index }}.qty" :label="__('Qty')" type="number" min="1" />
                        <flux:error name="items.{{ $index }}.qty" />
                    </div>
                    <div class="md:col-span-3">
                        <flux:input wire:model="items.{{ $index }}.price" :label="__('Price')" type="number" min="0" step="0.01" />
                        <flux:error name="items.{{ $index }}.price" />
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <flux:button
                            type="button"
                            variant="danger"
                            :disabled="count($items) === 1"
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Remove Item?', text: 'Item baris ini akan dihapus dari PO.', confirmButtonText: 'Ya, hapus' })) { $wire.removeItem({{ $index }}) } })()"
                        >
                            {{ __('X') }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>

        <flux:error name="items" />

        <div class="flex flex-wrap gap-2">
            <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
            <flux:button :href="route('pos.show', $purchaseOrder)" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</section>
