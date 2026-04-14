<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>APK Vendor | Vendor Procurement Platform</title>

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

                const labelMap = {
                    light: 'Light',
                    dark: 'Dark',
                    system: 'System',
                };

                const applyTheme = (theme) => {
                    const normalizedTheme = ['light', 'dark', 'system'].includes(theme) ? theme : 'light';
                    const shouldUseDark = normalizedTheme === 'dark' || (normalizedTheme === 'system' && media.matches);

                    document.documentElement.classList.toggle('dark', shouldUseDark);
                    window.localStorage.setItem(storageKey, normalizedTheme);

                    const themeLabel = document.getElementById('landing-theme-label');
                    if (themeLabel) {
                        themeLabel.textContent = labelMap[normalizedTheme];
                    }
                };

                const getTheme = () => window.localStorage.getItem(storageKey) ?? 'light';
                applyTheme(getTheme());

                window.addEventListener('DOMContentLoaded', () => {
                    const toggleButton = document.getElementById('landing-theme-toggle');
                    const menu = document.getElementById('landing-theme-menu');

                    if (toggleButton && menu) {
                        toggleButton.addEventListener('click', () => {
                            menu.classList.toggle('hidden');
                        });

                        document.addEventListener('click', (event) => {
                            if (!menu.contains(event.target) && !toggleButton.contains(event.target)) {
                                menu.classList.add('hidden');
                            }
                        });
                    }

                    document.querySelectorAll('[data-theme-option]').forEach((button) => {
                        button.addEventListener('click', () => {
                            applyTheme(button.getAttribute('data-theme-option'));
                            menu?.classList.add('hidden');
                        });
                    });

                    applyTheme(getTheme());
                });

                media.addEventListener('change', () => {
                    if (getTheme() === 'system') {
                        applyTheme('system');
                    }
                });
            })();
        </script>
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-24 top-0 h-96 w-96 rounded-full bg-cyan-300/20 blur-3xl dark:bg-cyan-500/20"></div>
            <div class="absolute right-0 top-1/3 h-96 w-96 rounded-full bg-emerald-300/15 blur-3xl dark:bg-emerald-500/15"></div>
            <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-amber-200/20 blur-3xl dark:bg-amber-500/10"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 sm:px-8 lg:px-12">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-zinc-200/70 bg-white/85 px-5 py-3 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/prochain.png') }}" alt="APK Vendor" class="h-10 w-10 rounded-lg object-contain" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Vendor Procurement Suite</p>
                        <h1 class="text-lg font-semibold">APK Vendor</h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <button
                            id="landing-theme-toggle"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800"
                        >
                            <span class="inline-block h-2 w-2 rounded-full bg-cyan-500"></span>
                            <span id="landing-theme-label">Light</span>
                            <span aria-hidden="true">&#9662;</span>
                        </button>

                        <div id="landing-theme-menu" class="absolute right-0 z-20 mt-2 hidden min-w-40 rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                            <button type="button" data-theme-option="light" class="w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">Light</button>
                            <button type="button" data-theme-option="dark" class="w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">Dark</button>
                            <button type="button" data-theme-option="system" class="w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">System</button>
                        </div>
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
                                    Register Vendor
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
                            Trusted Vendor Procurement Operations
                        </p>

                        <h2 class="text-4xl font-semibold leading-tight sm:text-5xl">
                            One platform to manage vendor onboarding, sourcing, and invoice governance.
                        </h2>

                        <p class="text-base text-zinc-600 dark:text-zinc-300">
                            APK Vendor membantu tim procurement menjalankan alur lengkap: registrasi vendor, RFQ, purchase order, goods receipt, dan approval invoice dalam satu kontrol operasional.
                        </p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <p class="text-xs uppercase tracking-[0.15em] text-zinc-500 dark:text-zinc-400">Vendor Performance View</p>
                                <p class="mt-2 text-2xl font-semibold">Realtime</p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <p class="text-xs uppercase tracking-[0.15em] text-zinc-500 dark:text-zinc-400">Approval Governance</p>
                                <p class="mt-2 text-2xl font-semibold">Controlled</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Operational Flow</p>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>Vendor Onboarding</span>
                                    <span class="font-semibold">Registration + Validation</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[82%] rounded-full bg-cyan-500"></div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>Sourcing & RFQ</span>
                                    <span class="font-semibold">Vendor Assignment</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[74%] rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span>PO to Invoice Control</span>
                                    <span class="font-semibold">Audit-ready</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                    <div class="h-full w-[88%] rounded-full bg-indigo-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-zinc-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                    <h3 class="text-xl font-semibold">Designed for Procurement and Vendor Collaboration</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Setiap tahap transaksi vendor dijalankan dengan kontrol role-permission, notifikasi in-app, dan histori dokumen terpusat.</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Vendor Registration</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Vendor membuat profil dan mengunggah dokumen pendukung.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>RFQ Management</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Tim procurement membuat RFQ dan menetapkan vendor terundang.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Purchase Execution</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">PO dibuat, barang diterima, dan status operasional dipantau.</p></div>
                        <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800"><strong>Invoice Approval</strong><p class="mt-1 text-zinc-600 dark:text-zinc-300">Invoice vendor diverifikasi admin sebelum keputusan akhir.</p></div>
                    </div>
                </section>
            </main>

            <footer class="mt-10 border-t border-zinc-200 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                <p>APK Vendor - Integrated vendor procurement platform for secure and traceable operations.</p>
            </footer>
        </div>
    </body>
</html>
