<?php

namespace App\Livewire\RFQ;

use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\RfqStatus;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('RFQ Details')]
class Show extends Component
{
    public Rfq $rfq;

    public ?int $selectedVendorId = null;

    public ?int $selectedResponseId = null;

    public bool $showResponseHistoryModal = false;

    /**
     * Mount the component.
     */
    public function mount(Rfq $rfq): void
    {
        Gate::authorize('view', $rfq);

        $this->rfq = $rfq;
    }

    /**
     * Close the RFQ.
     */
    public function closeRfq(): void
    {
        Gate::authorize('update', $this->rfq);

        $this->rfq->update([
            'status' => RfqStatus::Closed,
        ]);

        Flux::toast(text: __('RFQ has been closed.'));
    }

    /**
     * Delete current RFQ.
     */
    public function deleteRfq(): void
    {
        Gate::authorize('delete', $this->rfq);

        if ($this->rfq->status === RfqStatus::Closed) {
            Flux::toast(variant: 'warning', text: __('Closed RFQ cannot be deleted.'));

            return;
        }

        if ($this->rfq->purchaseOrders()->exists()) {
            Flux::toast(variant: 'danger', text: __('RFQ with linked purchase orders cannot be deleted.'));

            return;
        }

        $this->rfq->delete();

        Flux::toast(variant: 'success', text: __('RFQ deleted successfully.'));

        $this->redirect(route('rfqs.my', absolute: false), navigate: true);
    }

    /**
     * Redirect to PO creation with selected RFQ and vendor.
     */
    public function createPurchaseOrder(): void
    {
        Gate::authorize('create', PurchaseOrder::class);

        $validated = $this->validate([
            'selectedVendorId' => [
                'required',
                'integer',
                Rule::exists('rfq_responses', 'vendor_id')->where(
                    fn ($query) => $query->where('rfq_id', $this->rfq->id),
                ),
            ],
        ]);

        $this->redirect(
            route('pos.create', ['rfq' => $this->rfq->id, 'vendor' => $validated['selectedVendorId']], absolute: false),
            navigate: true,
        );
    }

    /**
     * Open response change history for a selected vendor response.
     */
    public function openResponseHistory(int $responseId): void
    {
        Gate::authorize('view', $this->rfq);

        $responseExists = $this->rfq->responses()
            ->whereKey($responseId)
            ->exists();

        if (! $responseExists) {
            abort(404);
        }

        $this->selectedResponseId = $responseId;
        $this->showResponseHistoryModal = true;
    }

    /**
     * Close response history modal.
     */
    public function closeResponseHistory(): void
    {
        $this->showResponseHistoryModal = false;
    }

    /**
     * Get selected response model.
     */
    private function selectedResponse(Collection $responses): ?RfqResponse
    {
        if ($this->selectedResponseId === null) {
            return null;
        }

        /** @var ?RfqResponse $response */
        $response = $responses->firstWhere('id', $this->selectedResponseId);

        return $response;
    }

    public function render(): View
    {
        $rfq = $this->rfq->load([
            'creator',
            'vendors.user',
            'responses.vendor.user',
        ]);

        $selectedResponse = $this->selectedResponse($rfq->responses);
        $responseHistoryLogs = collect();

        if ($selectedResponse !== null) {
            $responseHistoryLogs = Activity::query()
                ->where('subject_type', RfqResponse::class)
                ->where('subject_id', $selectedResponse->id)
                ->with('causer')
                ->latest()
                ->get();
        }

        return view('livewire.rfq.show', [
            'rfq' => $rfq,
            'selectedResponse' => $selectedResponse,
            'responseHistoryLogs' => $responseHistoryLogs,
        ]);
    }
}
