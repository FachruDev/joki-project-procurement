<x-layouts::auth :title="__('Confirm password')">
    <div class="space-y-6">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Confirm Password') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('This is a secure ProChain area. Re-enter your password to continue.') }}</flux:text>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-5">
            @csrf

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
