<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use App\Models\Vendor;
use App\RfqStatus;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit RFQ')]
class Edit extends Component
{
    public Rfq $rfq;

    public string $title = '';

    public string $description = '';

    public string $deadline = '';

    public string $status = 'open';

    /**
     * @var list<int>
     */
    public array $vendorIds = [];

    /**
     * Mount the component.
     */
    public function mount(Rfq $rfq): void
    {
        Gate::authorize('update', $rfq);

        $this->rfq = $rfq->load('vendors:id');
        $this->title = $rfq->title;
        $this->description = $rfq->description;
        $this->deadline = $rfq->deadline->format('Y-m-d\TH:i');
        $this->status = $rfq->status->value;
        $this->vendorIds = $rfq->vendors->pluck('id')->all();
    }

    /**
     * Persist RFQ updates.
     */
    public function save(): void
    {
        Gate::authorize('update', $this->rfq);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'deadline' => ['required', 'date', 'after:now'],
            'status' => ['required', Rule::in([RfqStatus::Open->value, RfqStatus::Closed->value])],
            'vendorIds' => ['required', 'array', 'min:1'],
            'vendorIds.*' => [
                'required',
                'integer',
                Rule::exists('vendors', 'id')->where(fn ($query) => $query->where('status', VendorStatus::Approved->value)),
            ],
        ]);

        $this->rfq->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            'status' => $validated['status'],
        ]);

        $this->rfq->vendors()->sync($validated['vendorIds']);

        Flux::toast(variant: 'success', text: __('RFQ updated successfully.'));

        $this->redirect(route('rfqs.show', $this->rfq, absolute: false), navigate: true);
    }

    /**
     * Get approved vendors available for assignment.
     *
     * @return Collection<int, Vendor>
     */
    #[Computed]
    public function availableVendors(): Collection
    {
        return Vendor::query()
            ->with('user')
            ->where('status', VendorStatus::Approved)
            ->orderBy('company_name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.rfq.edit');
    }
}
