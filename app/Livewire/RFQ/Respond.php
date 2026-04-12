<?php

namespace App\Livewire\RFQ;

use App\Models\Rfq;
use App\Models\RfqResponse;
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
        if ($this->alreadySubmitted) {
            $this->addError('price', __('You have already submitted a response for this RFQ.'));

            return;
        }

        Gate::authorize('submit', [RfqResponse::class, $this->rfq]);

        $validated = $this->validate([
            'price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $vendor = Auth::user()?->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $responseExists = RfqResponse::query()
            ->where('rfq_id', $this->rfq->id)
            ->where('vendor_id', $vendor->id)
            ->exists();

        if ($responseExists) {
            $this->alreadySubmitted = true;
            $this->addError('price', __('You have already submitted a response for this RFQ.'));

            return;
        }

        RfqResponse::create([
            'rfq_id' => $this->rfq->id,
            'vendor_id' => $vendor->id,
            'price' => $validated['price'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->alreadySubmitted = true;

        Flux::toast(variant: 'success', text: __('Response submitted successfully.'));

        $this->redirect(route('rfqs.show', $this->rfq, absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.rfq.respond');
    }
}
