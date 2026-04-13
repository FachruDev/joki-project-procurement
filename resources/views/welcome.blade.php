<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Procurement Platform') }} - {{ config('app.name') === 'Laravel' ? 'ProChain' : config('app.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            const appearance = window.localStorage.getItem('flux.appearance') ?? 'light';
            window.localStorage.setItem('flux.appearance', appearance);

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldUseDark = appearance === 'dark' || (appearance === 'system' && prefersDark);

            if (shouldUseDark) {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-20 top-0 h-80 w-80 rounded-full bg-cyan-400/25 blur-3xl dark:bg-cyan-500/20"></div>
            <div class="absolute -right-20 top-40 h-96 w-96 rounded-full bg-emerald-400/20 blur-3xl dark:bg-emerald-500/20"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-amber-300/20 blur-3xl dark:bg-amber-500/15"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 sm:px-8 lg:px-12">
            <header class="flex items-center justify-between rounded-2xl border border-zinc-200/70 bg-white/80 px-5 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/70">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">{{ __('Procurement Suite') }}</p>
                    <h1 class="text-lg font-semibold">{{ config('app.name', 'Procurement Platform') }}</h1>
                </div>

                <div class="flex items-center gap-2">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300">
                                {{ __('Go to Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                {{ __('Sign In') }}
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-500">
                                    {{ __('Register Vendor') }}
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </header>

            <main class="mt-10 flex-1 space-y-10">
                <section class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div class="space-y-5">
                        <p class="inline-flex w-fit items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700 dark:border-cyan-900 dark:bg-cyan-950/70 dark:text-cyan-300">
                            {{ __('End-to-end Procurement Workflow') }}
                        </p>

                        <h2 class="text-4xl font-semibold leading-tight sm:text-5xl">
                            {{ __('Manage Vendor Onboarding, RFQ, PO, GR, and Invoice Approval in One Place.') }}
                        </h2>

                        <p class="text-base text-zinc-600 dark:text-zinc-300">
                            {{ __('Built for Admin, Procurement, and Vendor teams with role-based access, approval gates, and real-time operational visibility.') }}
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-zinc-300 bg-white px-3 py-1 text-xs font-medium dark:border-zinc-700 dark:bg-zinc-900">{{ __('Admin') }}</span>
                            <span class="rounded-full border border-zinc-300 bg-white px-3 py-1 text-xs font-medium dark:border-zinc-700 dark:bg-zinc-900">{{ __('Procurement') }}</span>
                            <span class="rounded-full border border-zinc-300 bg-white px-3 py-1 text-xs font-medium dark:border-zinc-700 dark:bg-zinc-900">{{ __('Vendor') }}</span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">{{ __('Process Snapshot') }}</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Vendor Queue') }}</p>
                                <p class="mt-1 text-2xl font-semibold">{{ __('Approval Based') }}</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('RFQ Response') }}</p>
                                <p class="mt-1 text-2xl font-semibold">{{ __('One Submission') }}</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Document Handling') }}</p>
                                <p class="mt-1 text-2xl font-semibold">{{ __('MediaLibrary') }}</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Access Control') }}</p>
                                <p class="mt-1 text-2xl font-semibold">{{ __('Spatie Permission') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                    <h3 class="text-xl font-semibold">{{ __('Operational Flow') }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('The platform follows the procurement lifecycle below with permission-based checkpoints.') }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>1.</strong> {{ __('Vendor registers and submits profile documents.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>2.</strong> {{ __('Admin reviews and approves vendor account.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>3.</strong> {{ __('Procurement creates RFQ and assigns approved vendors.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>4.</strong> {{ __('Assigned vendor submits one RFQ response.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>5.</strong> {{ __('Procurement selects response and creates PO.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>6.</strong> {{ __('Goods receipt is recorded against PO.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>7.</strong> {{ __('Vendor uploads invoice document for approval.') }}</div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>8.</strong> {{ __('Admin approves or rejects invoice and completes cycle.') }}</div>
                    </div>
                </section>
            </main>

            <footer class="mt-10 border-t border-zinc-200 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                <p>{{ __('Procurement platform with role-based workflows, in-app notifications, and auditable document lifecycle.') }}</p>
            </footer>
        </div>
    </body>
</html>
