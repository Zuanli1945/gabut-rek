<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance="system">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[var(--cream)]">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-[var(--border-hair)] bg-[var(--paper)]">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Studio')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="beaker" :href="route('materials.index')" :current="request()->routeIs('materials.*')" wire:navigate>
                        {{ __('Materials') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('formulas.index')" :current="request()->routeIs('formulas.*')" wire:navigate>
                        {{ __('Formulas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="archive-box" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            @if(auth()->user()?->hasAnyRole(['owner', 'produksi', 'cs']))
            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Admin')" class="grid">
                    <flux:sidebar.item icon="command-line" :href="route('admin.dashboard')" :current="request()->routeIs('admin.*')" wire:navigate>
                        {{ __('Admin') }}
                    </flux:sidebar.item>
                    @can('role:owner,produksi')
                    <flux:sidebar.item icon="wrench" :href="route('admin.inventory.materials')" :current="request()->routeIs('admin.inventory.*')" wire:navigate>
                        {{ __('Inventory') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('role:owner,cs')
                    <flux:sidebar.item icon="users" :href="route('admin.crm.orders')" :current="request()->routeIs('admin.crm.*')" wire:navigate>
                        {{ __('CRM') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('role:owner')
                    <flux:sidebar.item icon="chart-bar" :href="route('admin.analytics')" :current="request()->routeIs('admin.analytics')" wire:navigate>
                        {{ __('Analytics') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            </flux:sidebar.nav>
            @endif

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/frandika06/docs-ai" target="_blank">
                    {{ __('docs-ai') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="{{ route('dashboard') }}" wire:navigate>
                    {{ __('Help') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

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
