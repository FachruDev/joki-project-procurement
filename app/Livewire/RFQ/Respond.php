<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Notifications\InAppNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Submit RFQ Response')]
class Respond extends Component
{
    public Rfq $rfq;

    public string $price = '';

    public ?string $notes = null;

    public bool $alreadySubmitted = false;

    public ?int $responseId = null;

    /**
     * Mount the component.
     */
    public function mount(Rfq $rfq): void
    {
        Gate::authorize('submit', [RfqResponse::class, $rfq]);

        $this->rfq = $rfq;

        $vendor = Auth::user()?->vendor;

        if ($vendor !== null) {
            $response = RfqResponse::query()
                ->where('rfq_id', $rfq->id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if ($response !== null) {
                $this->alreadySubmitted = true;
                $this->responseId = $response->id;
                $this->price = (string) $response->price;
                $this->notes = $response->notes;
            }
        }
    }

    /**
     * Submit RFQ response.
     */
    public function submit(): void
    {
        Gate::authorize('submit', [RfqResponse::class, $this->rfq]);

        $validated = $this->validate([
            'price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $vendor = Auth::user()?->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $existingResponse = RfqResponse::query()
            ->where('rfq_id', $this->rfq->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if ($existingResponse !== null) {
            $existingResponse->update([
                'price' => $validated['price'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->responseId = $existingResponse->id;
            $this->alreadySubmitted = true;
        } else {
            $createdResponse = RfqResponse::create([
                'rfq_id' => $this->rfq->id,
                'vendor_id' => $vendor->id,
                'price' => $validated['price'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->responseId = $createdResponse->id;
            $this->alreadySubmitted = true;
        }

        $this->rfq->loadMissing('creator');
        if ($this->rfq->creator !== null && ! $this->rfq->creator->is($vendor->user)) {
            $this->rfq->creator->notify(new InAppNotification(
                title: $existingResponse !== null ? __('RFQ Response Updated') : __('RFQ Response Received'),
                message: $existingResponse !== null
                    ? __(':company updated response for RFQ \":title\" with new price :price.', [
                        'company' => $vendor->company_name,
                        'title' => $this->rfq->title,
                        'price' => number_format((float) $validated['price'], 2),
                    ])
                    : __(':company submitted a response for RFQ \":title\" with price :price.', [
                        'company' => $vendor->company_name,
                        'title' => $this->rfq->title,
                        'price' => number_format((float) $validated['price'], 2),
                    ]),
                actionUrl: route('rfqs.show', $this->rfq, absolute: false),
                actionLabel: __('Review RFQ'),
                variant: 'info',
            ));
        }

        Flux::toast(
            variant: 'success',
            text: $existingResponse !== null
                ? __('Response updated successfully.')
                : __('Response submitted successfully.'),
        );

        $this->redirect(route('rfqs.show', $this->rfq, absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.rfq.respond');
    }
}
