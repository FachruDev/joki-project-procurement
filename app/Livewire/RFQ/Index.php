<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
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

    public function render(): View
    {
        $user = Auth::user();

        $rfqs = Rfq::query()
            ->with(['creator', 'vendors'])
            ->withCount('responses')
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
