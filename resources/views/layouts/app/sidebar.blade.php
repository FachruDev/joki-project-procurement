<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Main')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @can('vendor.manage')
                    <flux:sidebar.group :heading="__('Vendor')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('vendor.register')" :current="request()->routeIs('vendor.register')" wire:navigate>
                            {{ __('Vendor Review') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan

                @can('rfq.respond')
                    <flux:sidebar.group :heading="__('My Vendor')" class="grid">
                        <flux:sidebar.item icon="identification" :href="route('vendor.profile')" :current="request()->routeIs('vendor.profile')" wire:navigate>
                            {{ __('Vendor Profile') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan

                @can('rfq.view')
                    <flux:sidebar.group :heading="__('RFQ')" class="grid">
                        <flux:sidebar.item icon="document-text" :href="route('rfqs.index')" :current="request()->routeIs('rfqs.index')" wire:navigate>
                            {{ __('RFQ List') }}
                        </flux:sidebar.item>

                        @can('rfq.create')
                            <flux:sidebar.item icon="plus-circle" :href="route('rfqs.create')" :current="request()->routeIs('rfqs.create')" wire:navigate>
                                {{ __('Create RFQ') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endcan

                @can('po.view')
                    <flux:sidebar.group :heading="__('Purchase Orders')" class="grid">
                        <flux:sidebar.item icon="shopping-cart" :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>
                            {{ __('PO List') }}
                        </flux:sidebar.item>

                        @can('po.create')
                            <flux:sidebar.item icon="plus-circle" :href="route('pos.create')" :current="request()->routeIs('pos.create')" wire:navigate>
                                {{ __('Create PO') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endcan

                @can('invoice.approve')
                    <flux:sidebar.group :heading="__('Invoices')" class="grid">
                        <flux:sidebar.item icon="check-circle" :href="route('invoices.approve')" :current="request()->routeIs('invoices.approve')" wire:navigate>
                            {{ __('Invoice Approval') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
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
