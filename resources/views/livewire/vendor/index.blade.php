<section class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Vendor List') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Browse all registered vendors and open detail profiles.') }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model.live="statusFilter" class="w-44">
                    <option value="all">{{ __('All') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="approved">{{ __('Approved') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Search') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="company / user / email / phone" />
            </flux:field>
        </div>
    </div>

    @if ($editingVendorId)
        <form
            wire:submit="updateVendor"
            class="space-y-4 rounded-lg border border-zinc-200 p-5 dark:border-zinc-700"
            data-swal-confirm
            data-swal-title="Simpan perubahan vendor?"
            data-swal-text="Data vendor akan diperbarui."
            data-swal-icon="question"
        >
            <flux:heading>{{ __('Edit Vendor') }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Company Name') }}</flux:label>
                    <flux:input wire:model="companyName" type="text" />
                    <flux:error name="companyName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" type="text" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Address') }}</flux:label>
                <flux:textarea wire:model="address" rows="3" />
                <flux:error name="address" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="vendorStatus">
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="approved">{{ __('Approved') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </flux:select>
                <flux:error name="vendorStatus" />
            </flux:field>

            <flux:error name="editingVendorId" />

            <div class="flex flex-wrap gap-2">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" wire:click="cancelEdit">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @endif

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Vendor') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Contact') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('RFQ Joined') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('PO Won') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Docs') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Total Transaction') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($vendors as $vendor)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $vendor->company_name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ \Illuminate\Support\Str::limit($vendor->address, 70) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $vendor->user?->name }}</div>
                            <div class="text-zinc-500">{{ $vendor->user?->email }}</div>
                            <div class="text-zinc-500">{{ $vendor->phone }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$vendor->status->value === 'approved' ? 'green' : ($vendor->status->value === 'pending' ? 'amber' : 'red')">
                                {{ strtoupper($vendor->status->value) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ $vendor->rfqs_count }}</td>
                        <td class="px-4 py-3">{{ $vendor->purchase_orders_count }}</td>
                        <td class="px-4 py-3">{{ $vendor->documents_count }}</td>
                        <td class="px-4 py-3">{{ number_format((float) ($vendor->total_transaction_amount ?? 0), 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" :href="route('vendor.show', $vendor)" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>
                                <flux:button size="sm" wire:click="startEdit({{ $vendor->id }})">{{ __('Edit') }}</flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Delete Vendor?', text: 'Vendor ini akan dihapus permanen.', icon: 'warning', confirmButtonText: 'Ya, hapus' })) { $wire.deleteVendor({{ $vendor->id }}) } })()"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-zinc-500">{{ __('No vendors found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $vendors->links() }}</div>
</section>
