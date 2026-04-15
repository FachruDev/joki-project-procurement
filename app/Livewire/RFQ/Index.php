<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use App\RfqStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('RFQ List')]
class Index extends Component
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

        if ($rfq->status === RfqStatus::Closed) {
            Flux::toast(variant: 'warning', text: __('Closed RFQ cannot be deleted.'));

            return;
        }

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

        $rfqs = Rfq::query()
            ->with(['creator', 'vendors.user', 'responses.vendor'])
            ->withCount(['responses', 'vendors'])
            ->when(
                $user?->vendor !== null && $user?->can('vendor.manage') === false,
                fn ($query) => $query->whereHas('vendors', fn ($vendorQuery) => $vendorQuery->whereKey($user->vendor->id)),
            )
            ->latest()
            ->get();

        return view('livewire.rfq.index', [
            'rfqs' => $rfqs,
        ]);
    }
}
