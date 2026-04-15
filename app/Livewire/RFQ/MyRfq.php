<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My RFQ')]
class MyRfq extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Rfq::class);
    }

    /**
     * Delete an RFQ record.
     */
    public function deleteRfq(int $rfqId): void
    {
        $rfq = Rfq::query()->findOrFail($rfqId);

        Gate::authorize('delete', $rfq);

        if ($rfq->purchaseOrders()->exists()) {
            Flux::toast(variant: 'danger', text: __('RFQ with linked purchase orders cannot be deleted.'));

            return;
        }

        $rfq->delete();

        Flux::toast(variant: 'success', text: __('RFQ deleted successfully.'));
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $rfqs = Rfq::query()
            ->with(['creator', 'vendors.user'])
            ->withCount('responses')
            ->when(
                $user->vendor !== null,
                fn ($query) => $query->whereHas('vendors', fn ($vendorQuery) => $vendorQuery->whereKey($user->vendor->id)),
            )
            ->when(
                $user->vendor === null && $user->hasRole('Procurement'),
                fn ($query) => $query->where('created_by', $user->id),
            )
            ->latest()
            ->get();

        return view('livewire.rfq.my-rfq', [
            'rfqs' => $rfqs,
        ]);
    }
}
