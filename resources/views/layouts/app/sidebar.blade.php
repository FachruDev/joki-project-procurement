<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

                <flux:spacer />

                <div class="hidden lg:block">
                    <livewire:notifications.center :compact="true" key="notif-desktop" />
                </div>

                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @php
                    $currentUser = auth()->user();
                    $isApprovedVendor = $currentUser->vendor?->status?->value === 'approved';
                    $isPendingVendorWithoutManagement = $currentUser->vendor !== null
                        && $isApprovedVendor === false
                        && $currentUser->can('vendor.manage') === false;
                    $profileRoute = $currentUser->can('rfq.respond') ? route('vendor.profile') : route('profile.edit');
                    $profileMedia = $currentUser->getFirstMedia('profile-images');
                    $profileImageUrl = $profileMedia !== null ? route('media.show', $profileMedia) : null;
                @endphp

                <flux:sidebar.group icon="home" :heading="__('Main')" expandable :expanded="request()->routeIs('dashboard') || request()->routeIs('reports.index')">
                    <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @can('report.view')
                        <flux:sidebar.item :href="route('reports.index')" :current="request()->routeIs('reports.index')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>

                @if (auth()->user()->can('user.manage') || auth()->user()->can('permission.manage'))
                    <flux:sidebar.group icon="shield-check" :heading="__('Administration')" expandable :expanded="request()->routeIs('management.*')">
                        @can('user.manage')
                            <flux:sidebar.item :href="route('management.users')" :current="request()->routeIs('management.users*')" wire:navigate>
                                {{ __('User Management') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('permission.manage')
                            <flux:sidebar.item :href="route('management.permissions')" :current="request()->routeIs('management.permissions*') || request()->routeIs('management.roles.*')" wire:navigate>
                                {{ __('Role & Permission') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                @can('vendor.manage')
                    <flux:sidebar.group icon="users" :heading="__('Vendor')" expandable :expanded="request()->routeIs('vendor.register') || request()->routeIs('vendor.index') || request()->routeIs('vendor.show')">
                        <flux:sidebar.item :href="route('vendor.index')" :current="request()->routeIs('vendor.index') || request()->routeIs('vendor.show')" wire:navigate>
                            {{ __('Vendor List') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item :href="route('vendor.register')" :current="request()->routeIs('vendor.register')" wire:navigate>
                            {{ __('Vendor Review') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan

                @if (! $isPendingVendorWithoutManagement && $currentUser->canAny(['rfq.view', 'rfq.create']))
                    <flux:sidebar.group icon="document-text" :heading="__('RFQ')" expandable :expanded="request()->routeIs('rfqs.*')">
                        @can('rfq.view')
                            <flux:sidebar.item :href="route('rfqs.index')" :current="request()->routeIs('rfqs.index')" wire:navigate>
                                {{ __('RFQ List') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item :href="route('rfqs.my')" :current="request()->routeIs('rfqs.my')" wire:navigate>
                                {{ __('My RFQ') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                @if (! $isPendingVendorWithoutManagement && $currentUser->canAny(['po.view', 'po.create']))
                    <flux:sidebar.group icon="shopping-cart" :heading="__('Purchase Orders')" expandable :expanded="request()->routeIs('pos.*') || request()->routeIs('gr.create')">
                        @can('po.view')
                            <flux:sidebar.item :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>
                                {{ __('PO List') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item :href="route('pos.my')" :current="request()->routeIs('pos.my')" wire:navigate>
                                {{ __('My PO') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                @php
                    $canViewInvoiceList = $currentUser->can('invoice.view');
                    $canViewMyInvoice = $currentUser->can('invoice.view') && ($currentUser->can('po.view') || $currentUser->can('invoice.upload'));
                @endphp

                @if (! $isPendingVendorWithoutManagement && (auth()->user()->can('invoice.approve') || $canViewInvoiceList || $canViewMyInvoice))
                    <flux:sidebar.group icon="receipt-percent" :heading="__('Invoices')" expandable :expanded="request()->routeIs('invoices.*')">
                        @if ($canViewInvoiceList)
                            <flux:sidebar.item :href="route('invoices.list')" :current="request()->routeIs('invoices.list')" wire:navigate>
                                {{ __('Invoice List') }}
                            </flux:sidebar.item>
                        @endif

                        @if ($canViewMyInvoice)
                            <flux:sidebar.item :href="route('invoices.my')" :current="request()->routeIs('invoices.my')" wire:navigate>
                                {{ __('My Invoice') }}
                            </flux:sidebar.item>
                        @endif

                        @can('invoice.approve')
                            <flux:sidebar.item :href="route('invoices.approve')" :current="request()->routeIs('invoices.approve')" wire:navigate>
                                {{ __('Invoice Approval') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <livewire:notifications.center :compact="true" key="notif-mobile" />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    :avatar="$profileImageUrl"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    :src="$profileImageUrl"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.group :heading="__('Theme')">
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">
                            {{ __('Light') }}
                        </flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">
                            {{ __('Dark') }}
                        </flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">
                            {{ __('System') }}
                        </flux:menu.item>
                    </flux:menu.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="$profileRoute" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="w-full"
                        data-swal-confirm
                        data-swal-title="Log out?"
                        data-swal-text="Anda akan keluar dari sesi aplikasi."
                        data-swal-icon="question"
                        data-swal-confirm-text="Ya, keluar"
                    >
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
