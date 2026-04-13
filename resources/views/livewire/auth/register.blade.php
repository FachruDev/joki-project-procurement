<x-layouts::auth :title="__('Register')">
    <div class="space-y-6">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Create ProChain Vendor Account') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">
                {{ __('Register your account and vendor profile to join the procurement process.') }}
            </flux:text>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                <div>
                    <flux:heading size="base">{{ __('Account Information') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Used for login and access authorization.') }}</flux:text>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input
                        name="name"
                        :label="__('Full Name')"
                        :value="old('name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="__('Full name')"
                    />

                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Password')"
                        viewable
                    />

                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirm password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Confirm password')"
                        viewable
                    />
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                <div>
                    <flux:heading size="base">{{ __('Vendor Profile') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('These details will be reviewed before vendor approval.') }}</flux:text>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input
                        name="company_name"
                        :label="__('Company Name')"
                        :value="old('company_name')"
                        type="text"
                        autocomplete="organization"
                        :placeholder="__('Your company name')"
                    />

                    <flux:input
                        name="phone"
                        :label="__('Phone')"
                        :value="old('phone')"
                        type="text"
                        autocomplete="tel"
                        :placeholder="__('Phone number')"
                    />
                </div>

                <flux:textarea
                    name="address"
                    :label="__('Address')"
                    rows="3"
                    :placeholder="__('Company address')"
                >{{ old('address') }}</flux:textarea>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create ProChain Account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
