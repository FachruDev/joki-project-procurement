    <section class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Upload Invoice') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Purchase Order #') }}{{ $purchaseOrder->id }}</flux:text>
        </div>

        @if ($existingInvoice)
            <flux:callout icon="document-text" variant="secondary">
                {{ __('Current invoice status:') }} {{ strtoupper($existingInvoice->status->value) }}
                @php
                    $existingInvoiceMedia = $existingInvoice->getFirstMedia('invoice-files');
                @endphp
                @if ($existingInvoiceMedia !== null)
                    <a href="{{ route('media.show', $existingInvoiceMedia) }}" target="_blank" class="ms-2 text-blue-600 underline dark:text-blue-400">
                        {{ __('View file') }}
                    </a>
                @endif
            </flux:callout>
        @endif

        <form
            wire:submit="save"
            class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
            data-swal-confirm
            data-swal-title="Upload Invoice?"
            data-swal-text="Dokumen invoice akan dikirim untuk proses approval."
            data-swal-icon="question"
        >
            <flux:field>
                <flux:label>{{ __('Invoice File') }}</flux:label>
                <flux:input wire:model="invoiceFile" type="file" />
                <flux:error name="invoiceFile" />
            </flux:field>

            <flux:button type="submit" variant="primary">{{ __('Upload Invoice') }}</flux:button>
        </form>
    </section>
