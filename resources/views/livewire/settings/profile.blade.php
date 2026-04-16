<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            @php
                $profileImageMedia = auth()->user()?->getFirstMedia('profile-images');
            @endphp

            <flux:field>
                <flux:label>{{ __('Profile Image') }}</flux:label>
                <flux:input wire:model="profileImage" type="file" accept="image/*" />
                <flux:error name="profileImage" />
            </flux:field>

            @if ($profileImageMedia !== null)
                <a href="{{ route('media.show', $profileImageMedia) }}" target="_blank" class="text-sm text-blue-600 underline dark:text-blue-400">
                    {{ __('View current profile image') }}
                </a>
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
