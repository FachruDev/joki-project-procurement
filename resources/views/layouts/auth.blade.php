@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-24 top-0 h-96 w-96 rounded-full bg-cyan-300/20 blur-3xl dark:bg-cyan-500/20"></div>
            <div class="absolute -right-28 bottom-0 h-96 w-96 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/15"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-5">
                <aside class="hidden lg:flex lg:col-span-2">
                    <div class="w-full rounded-3xl border border-zinc-200 bg-white/90 p-8 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                            <x-app-logo-icon class="h-11 w-11 rounded-xl object-contain" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Vendor Procurement Platform</p>
                                <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">APK Vendor</p>
                            </div>
                        </a>

                        <div class="mt-10 space-y-4">
                            <flux:heading size="xl">{{ __('Procurement Operations Without Fragmented Workflows') }}</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-300">
                                {{ __('Manage vendor onboarding, RFQ responses, purchase orders, goods receipts, and invoice approvals in one governed system.') }}
                            </flux:text>
                        </div>

                        <div class="mt-8 space-y-3 text-sm">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Role & Permission Control') }}</p>
                                <p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Access based on dynamic permissions with protected SuperAdmin governance.') }}</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Operational Visibility') }}</p>
                                <p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Live dashboard summary and procurement reports for faster decisions.') }}</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/60">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Document Governance') }}</p>
                                <p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('MediaLibrary-based files with auditable process checkpoints.') }}</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="lg:col-span-3">
                    <div class="mx-auto w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                        <a href="{{ route('home') }}" class="mb-6 flex items-center gap-3 lg:hidden" wire:navigate>
                            <x-app-logo-icon class="h-10 w-10 rounded-lg object-contain" />
                            <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">APK Vendor</span>
                        </a>

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
