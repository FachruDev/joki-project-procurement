<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Vendor Registration Review')]
class Register extends Component
{
    public string $statusFilter = 'pending';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Vendor::class);
    }

    /**
     * Approve a vendor profile.
     */
    public function approve(int $vendorId): void
    {
        $vendor = Vendor::query()->findOrFail($vendorId);

        Gate::authorize('approve', $vendor);

        $vendor->update([
            'status' => VendorStatus::Approved,
        ]);

        Flux::toast(variant: 'success', text: __('Vendor approved successfully.'));
    }

    /**
     * Reject a vendor profile.
     */
    public function reject(int $vendorId): void
    {
        $vendor = Vendor::query()->findOrFail($vendorId);

        Gate::authorize('approve', $vendor);

        $vendor->update([
            'status' => VendorStatus::Rejected,
        ]);

        Flux::toast(variant: 'warning', text: __('Vendor rejected.'));
    }

    /**
     * Get vendors based on selected status filter.
     */
    #[Computed]
    public function vendors(): Collection
    {
        return Vendor::query()
            ->with('user')
            ->withCount('documents')
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.vendor.register');
    }
}
