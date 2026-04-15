    <section class="space-y-8">
        <div>
            <flux:heading size="xl">{{ __('Vendor Profile') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your company details and upload supporting documents.') }}</flux:text>
        </div>

        <flux:callout icon="information-circle" variant="secondary">
            <div class="flex items-center justify-between gap-2">
                <div>
                    {{ __('Current approval status:') }}
                    <flux:badge class="ms-2" :color="$this->vendorProfile->status->value === 'approved' ? 'green' : ($this->vendorProfile->status->value === 'rejected' ? 'red' : 'amber')">
                        {{ ucfirst($this->vendorProfile->status->value) }}
                    </flux:badge>
                </div>
            </div>
        </flux:callout>

        <form
            wire:submit="saveProfile"
            class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
            data-swal-confirm
            data-swal-title="Simpan Profil Vendor?"
            data-swal-text="Perubahan profil perusahaan akan disimpan."
            data-swal-icon="question"
        >
            <flux:heading>{{ __('Company Details') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('Company Name') }}</flux:label>
                <flux:input wire:model="companyName" type="text" />
                <flux:error name="companyName" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Address') }}</flux:label>
                <flux:textarea wire:model="address" rows="3" />
                <flux:error name="address" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input wire:model="phone" type="text" />
                <flux:error name="phone" />
            </flux:field>

            <flux:button type="submit" variant="primary">{{ __('Save Profile') }}</flux:button>
        </form>

        <form
            wire:submit="uploadDocument"
            class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
            data-swal-confirm
            data-swal-title="Upload Dokumen Vendor?"
            data-swal-text="Pastikan dokumen yang diunggah sudah benar."
            data-swal-icon="question"
        >
            <flux:heading>{{ __('Upload Vendor Document') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('Document Type') }}</flux:label>
                <flux:input wire:model="documentType" type="text" placeholder="business_license" />
                <flux:error name="documentType" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('File') }}</flux:label>
                <flux:input wire:model="documentFile" type="file" />
                <flux:error name="documentFile" />
            </flux:field>

            <flux:button type="submit" variant="primary">{{ __('Upload Document') }}</flux:button>
        </form>

        <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading>{{ __('Uploaded Documents') }}</flux:heading>

            <div class="mt-4 space-y-3">
                @forelse ($this->vendorProfile->documents as $document)
                    <div class="flex items-center justify-between rounded-md border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <div>
                            <div class="font-medium">{{ $document->document_type }}</div>
                            @php
                                $documentMedia = $document->getFirstMedia('documents');
                            @endphp
                            @if ($documentMedia !== null)
                                <a href="{{ route('media.show', $documentMedia) }}" target="_blank" class="text-sm text-blue-600 underline dark:text-blue-400">
                                    {{ __('View file') }}
                                </a>
                            @endif
                        </div>
                        <flux:button
                            size="sm"
                            variant="danger"
                            x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Hapus Dokumen?', text: 'Dokumen vendor ini akan dihapus permanen.', confirmButtonText: 'Ya, hapus' })) { $wire.deleteDocument({{ $document->id }}) } })()"
                        >
                            {{ __('Delete') }}
                        </flux:button>
                    </div>
                @empty
                    <flux:text class="mt-3">{{ __('No documents uploaded yet.') }}</flux:text>
                @endforelse
            </div>
        </div>
    </section>
