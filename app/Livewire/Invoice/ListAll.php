<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Invoice List')]
class ListAll extends Component
{
    use WithPagination;

    public string $searchVendor = '';

    public string $statusFilter = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 10;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Invoice::class);
    }

    public function updatedSearchVendor(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $invoices = Invoice::query()
            ->with(['vendor.user', 'purchaseOrder', 'media'])
            ->when(
                $user->vendor !== null,
                fn ($query) => $query->where('vendor_id', $user->vendor->id),
            )
            ->when(
                $user->vendor === null && ! $user->can('invoice.approve') && ! $user->can('vendor.manage'),
                fn ($query) => $query->whereHas('purchaseOrder', fn ($purchaseOrderQuery) => $purchaseOrderQuery->where('created_by', $user->id)),
            )
            ->when(
                $this->searchVendor !== '',
                fn ($query) => $query->whereHas('vendor', fn ($vendorQuery) => $vendorQuery
                    ->where('company_name', 'like', '%'.$this->searchVendor.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$this->searchVendor.'%'))),
            )
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->when(
                $this->dateFrom !== '',
                fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom),
            )
            ->when(
                $this->dateTo !== '',
                fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo),
            )
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.invoice.list-all', [
            'invoices' => $invoices,
        ]);
    }
}
