<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
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

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Vendor::class);
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
