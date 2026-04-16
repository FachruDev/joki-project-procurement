<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use App\Notifications\InAppNotification;
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

        $vendor->loadMissing('user');
        $vendor->user?->notify(new InAppNotification(
            title: __('Vendor Approved'),
            message: __('Your vendor profile for :company has been approved. You can now respond to RFQs and upload invoices.', [
                'company' => $vendor->company_name,
            ]),
            actionUrl: route('dashboard'),
            actionLabel: __('Open Dashboard'),
            variant: 'success',
        ));

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

        $vendor->loadMissing('user');
        $vendor->user?->notify(new InAppNotification(
            title: __('Vendor Rejected'),
            message: __('Your vendor profile for :company was rejected. Please update your profile and documents, then resubmit.', [
                'company' => $vendor->company_name,
            ]),
            actionUrl: route('vendor.profile'),
            actionLabel: __('Update Profile'),
            variant: 'warning',
        ));

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
