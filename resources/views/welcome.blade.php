<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ProChain Procurement Hub</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const storageKey = 'flux.appearance';
                const media = window.matchMedia('(prefers-color-scheme: dark)');

                const applyTheme = (theme) => {
                    const shouldUseDark = theme === 'dark' || (theme === 'system' && media.matches);
                    document.documentElement.classList.toggle('dark', shouldUseDark);
                    window.localStorage.setItem(storageKey, theme);

                    document.querySelectorAll('[data-theme]').forEach((button) => {
                        const isActive = button.getAttribute('data-theme') === theme;
                        button.classList.toggle('bg-zinc-900', isActive);
                        button.classList.toggle('text-white', isActive);
                        button.classList.toggle('dark:bg-zinc-100', isActive);
                        button.classList.toggle('dark:text-zinc-900', isActive);
                        button.classList.toggle('bg-transparent', !isActive);
                        button.classList.toggle('text-zinc-600', !isActive);
                        button.classList.toggle('dark:text-zinc-300', !isActive);
                    });
                };

                const initialTheme = window.localStorage.getItem(storageKey) ?? 'light';
                applyTheme(initialTheme);

                window.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('[data-theme]').forEach((button) => {
                        button.addEventListener('click', () => applyTheme(button.getAttribute('data-theme')));
                    });

                    applyTheme(window.localStorage.getItem(storageKey) ?? 'light');
                });

                media.addEventListener('change', () => {
                    const theme = window.localStorage.getItem(storageKey) ?? 'light';
                    if (theme === 'system') {
                        applyTheme('system');
                    }
                });
            })();
        </script>
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-24 top-0 h-96 w-96 rounded-full bg-cyan-300/25 blur-3xl dark:bg-cyan-500/20"></div>
            <div class="absolute right-0 top-1/3 h-96 w-96 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/15"></div>
            <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-amber-200/20 blur-3xl dark:bg-amber-500/10"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 sm:px-8 lg:px-12">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-zinc-200/70 bg-white/85 px-5 py-3 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/prochain.png') }}" alt="ProChain" class="h-10 w-10 rounded-lg object-contain" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Procurement Platform</p>
                        <h1 class="text-lg font-semibold">ProChain</h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-900">
                        <button type="button" data-theme="light" class="rounded-md px-3 py-1 text-xs font-medium transition">Light</button>
                        <button type="button" data-theme="dark" class="rounded-md px-3 py-1 text-xs font-medium transition">Dark</button>
                        <button type="button" data-theme="system" class="rounded-md px-3 py-1 text-xs font-medium transition">System</button>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300">
                                Open Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                Sign In
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-500">
                                    Vendor Registration
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
                            Centralized Procurement Control
                        </p>

                        <h2 class="text-4xl font-semibold leading-tight sm:text-5xl">
                            End-to-end procurement execution for teams, vendors, and approvals.
                        </h2>

                        <p class="text-base text-zinc-600 dark:text-zinc-300">
                            ProChain provides a single workspace for vendor onboarding, RFQ coordination, purchase order execution, goods receipt tracking, and invoice approval decisions.
                        </p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <p class="text-xs uppercase tracking-[0.15em] text-zinc-500 dark:text-zinc-400">Monthly RFQ</p>
                                <p class="mt-2 text-2xl font-semibold">146</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <p class="text-xs uppercase tracking-[0.15em] text-zinc-500 dark:text-zinc-400">PO Completion</p>
                                <p class="mt-2 text-2xl font-semibold">92.4%</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Dummy Operations Snapshot</p>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>Vendor Review Queue</span>
                                    <span class="font-semibold">18</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[58%] rounded-full bg-amber-500"></div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>PO Requiring GR</span>
                                    <span class="font-semibold">11</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[40%] rounded-full bg-cyan-500"></div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>Invoice Pending Approval</span>
                                    <span class="font-semibold">27</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[71%] rounded-full bg-indigo-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                    <h3 class="text-xl font-semibold">Procurement Workflow Dummy Content</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Example process stages for a typical enterprise procurement cycle.</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Stage 1</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Vendor registration and profile validation.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Stage 2</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">RFQ publishing and vendor assignment.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Stage 3</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Commercial response evaluation and PO release.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Stage 4</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Delivery confirmation and invoice approval.</p></div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h4 class="font-semibold">Vendor Intelligence</h4>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Track vendor response rate, PO conversion, and transaction volume in one view.</p>
                    </article>
                    <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h4 class="font-semibold">RFQ Governance</h4>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Maintain transparent assignment, submission deadlines, and sourcing competition.</p>
                    </article>
                    <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h4 class="font-semibold">Payment Control</h4>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Monitor invoice bottlenecks and approval timing before payment release.</p>
                    </article>
                </section>
            </main>

            <footer class="mt-10 border-t border-zinc-200 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                <p>ProChain dummy landing content for procurement showcase and UI theme demonstration.</p>
            </footer>
        </div>
    </body>
</html>
