<?php

namespace App\Livewire\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\InAppNotification;
use App\VendorStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Vendor Profile')]
class Profile extends Component
{
    use WithFileUploads;

    public string $userName = '';

    public string $userEmail = '';

    public string $companyName = '';

    public string $address = '';

    public string $phone = '';

    public string $documentType = '';

    public mixed $documentFile = null;

    public mixed $profileImage = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'company_name' => $user->name,
            'address' => 'Address not provided',
            'phone' => '-',
            'status' => VendorStatus::Pending,
        ]);

        Gate::authorize('update', $vendor);

        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->companyName = $vendor->company_name;
        $this->address = $vendor->address;
        $this->phone = $vendor->phone;
    }

    /**
     * Save vendor profile details.
     */
    public function saveProfile(): void
    {
        $authUser = Auth::user();

        if ($authUser === null) {
            abort(401);
        }

        $validated = $this->validate([
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($authUser->id)],
            'companyName' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:100'],
            'profileImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $vendor = $this->vendorProfile;

        Gate::authorize('update', $vendor);

        $authUser->fill([
            'name' => $validated['userName'],
            'email' => $validated['userEmail'],
        ]);

        if ($authUser->isDirty('email')) {
            $authUser->email_verified_at = null;
        }

        if ($validated['profileImage'] ?? false) {
            $media = $authUser
                ->addMedia($validated['profileImage'])
                ->toMediaCollection('profile-images');

            $authUser->profile_image = $media->uuid;
        }

        $authUser->save();

        $vendor->update([
            'company_name' => $validated['companyName'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ]);

        $this->reset('profileImage');

        Flux::toast(variant: 'success', text: __('Vendor profile updated.'));
    }

    /**
     * Upload a supporting document.
     */
    public function uploadDocument(): void
    {
        $validated = $this->validate([
            'documentType' => ['required', 'string', 'max:255'],
            'documentFile' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = $this->vendorProfile;

        Gate::authorize('update', $vendor);

        $document = $vendor->documents()->create([
            'document_type' => $validated['documentType'],
        ]);

        $document->addMedia($validated['documentFile'])->toMediaCollection('documents');

        User::query()
            ->permission('vendor.approve')
            ->whereKeyNot(Auth::id())
            ->get()
            ->each(function (User $adminUser) use ($vendor, $document): void {
                $adminUser->notify(new InAppNotification(
                    title: __('Vendor Document Uploaded'),
                    message: __(':company uploaded a :document_type document and is ready for review.', [
                        'company' => $vendor->company_name,
                        'document_type' => $document->document_type,
                    ]),
                    actionUrl: route('vendor.register'),
                    actionLabel: __('Review Vendor'),
                    variant: 'info',
                ));
            });

        $this->reset('documentType', 'documentFile');

        Flux::toast(variant: 'success', text: __('Document uploaded successfully.'));
    }

    /**
     * Delete a vendor document.
     */
    public function deleteDocument(int $documentId): void
    {
        $vendor = $this->vendorProfile;

        Gate::authorize('update', $vendor);

        $document = $vendor->documents()->whereKey($documentId)->firstOrFail();

        $document->delete();

        Flux::toast(text: __('Document deleted.'));
    }

    /**
     * Get the current user's vendor profile.
     */
    #[Computed]
    public function vendorProfile(): Vendor
    {
        return Auth::user()
            ->vendor()
            ->with('documents.media')
            ->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.vendor.profile');
    }
}
