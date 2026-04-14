<x-layouts::auth :title="__('Email verification')">
    <div class="space-y-6">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Verify Your Email') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('Activate your APK Vendor account by completing email verification.') }}</flux:text>
        </div>

        <flux:text class="text-center">
            {{ __('Please verify your email address by clicking on the link we just emailed to you for APK Vendor access.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Resend verification email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
