<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use App\Models\Vendor;
use App\Notifications\InAppNotification;
use App\RfqStatus;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create RFQ')]
class Create extends Component
{
    public string $title = '';

    public string $description = '';

    public string $deadline = '';

    /**
     * @var list<int>
     */
    public array $vendorIds = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('create', Rfq::class);
    }

    /**
     * Store a new RFQ.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'deadline' => ['required', 'date', 'after:now'],
            'vendorIds' => ['required', 'array', 'min:1'],
            'vendorIds.*' => [
                'required',
                'integer',
                Rule::exists('vendors', 'id')->where(fn ($query) => $query->where('status', VendorStatus::Approved->value)),
            ],
        ]);

        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $rfq = DB::transaction(function () use ($validated, $user): Rfq {
            $rfq = Rfq::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'deadline' => $validated['deadline'],
                'created_by' => $user->id,
                'status' => RfqStatus::Open,
            ]);

            $rfq->vendors()->sync($validated['vendorIds']);

            return $rfq;
        });

        $rfq->loadMissing('vendors.user');
        foreach ($rfq->vendors as $assignedVendor) {
            $assignedVendor->user?->notify(new InAppNotification(
                title: __('New RFQ Invitation'),
                message: __('You are assigned to RFQ \":title\". Submit your response before :deadline.', [
                    'title' => $rfq->title,
                    'deadline' => $rfq->deadline?->format('Y-m-d H:i'),
                ]),
                actionUrl: route('rfqs.respond', $rfq),
                actionLabel: __('Submit Response'),
                variant: 'info',
            ));
        }

        Flux::toast(variant: 'success', text: __('RFQ created successfully.'));

        $this->redirect(route('rfqs.show', $rfq, absolute: false), navigate: true);
    }

    /**
     * Get approved vendors available for assignment.
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
        return view('livewire.rfq.create');
    }
}
