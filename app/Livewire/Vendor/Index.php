<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Vendor List')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public int $perPage = 10;

    public ?int $editingVendorId = null;

    public string $companyName = '';

    public string $address = '';

    public string $phone = '';

    public string $vendorStatus = 'pending';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Vendor::class);
    }

    /**
     * Load selected vendor into edit form.
     */
    public function startEdit(int $vendorId): void
    {
        $vendor = Vendor::query()->findOrFail($vendorId);

        Gate::authorize('update', $vendor);

        $this->editingVendorId = $vendor->id;
        $this->companyName = $vendor->company_name;
        $this->address = $vendor->address;
        $this->phone = $vendor->phone;
        $this->vendorStatus = $vendor->status->value;
    }

    /**
     * Reset edit form state.
     */
    public function cancelEdit(): void
    {
        $this->reset('editingVendorId', 'companyName', 'address', 'phone', 'vendorStatus');
    }

    /**
     * Persist vendor update.
     */
    public function updateVendor(): void
    {
        $validated = $this->validate([
            'editingVendorId' => ['required', 'integer', Rule::exists('vendors', 'id')],
            'companyName' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:100'],
            'vendorStatus' => ['required', Rule::in([VendorStatus::Pending->value, VendorStatus::Approved->value, VendorStatus::Rejected->value])],
        ]);

        $vendor = Vendor::query()->findOrFail($validated['editingVendorId']);

        Gate::authorize('update', $vendor);

        $vendor->update([
            'company_name' => $validated['companyName'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'status' => $validated['vendorStatus'],
        ]);

        $this->cancelEdit();

        Flux::toast(variant: 'success', text: __('Vendor updated successfully.'));
    }

    /**
     * Delete vendor profile.
     */
    public function deleteVendor(int $vendorId): void
    {
        $vendor = Vendor::query()->withCount('purchaseOrders')->findOrFail($vendorId);

        Gate::authorize('delete', $vendor);

        if ($vendor->purchase_orders_count > 0) {
            Flux::toast(variant: 'danger', text: __('Vendor with purchase order history cannot be deleted.'));

            return;
        }

        $vendor->delete();

        if ($this->editingVendorId === $vendorId) {
            $this->cancelEdit();
        }

        Flux::toast(variant: 'success', text: __('Vendor deleted successfully.'));
    }

    public function render(): View
    {
        $vendors = Vendor::query()
            ->with('user:id,name,email')
            ->withCount(['documents', 'rfqs', 'purchaseOrders'])
            ->withSum('purchaseOrders as total_transaction_amount', 'total_price')
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(function ($searchQuery): void {
                    $searchQuery
                        ->where('company_name', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%'));
                }),
            )
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.vendor.index', [
            'vendors' => $vendors,
        ]);
    }
}
