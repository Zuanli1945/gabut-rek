<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[var(--cream)] antialiased dark:bg-[var(--cream)]">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            {{-- Brand panel — perfumery visual --}}
            <div class="relative hidden h-full flex-col p-10 text-[var(--ink)] lg:flex dark:border-e dark:border-[var(--border-hair)]" style="background: var(--paper);">
                <div class="absolute inset-0 opacity-[0.03]" style="background: radial-gradient(ellipse at 30% 20%, var(--amber) 0%, transparent 60%), radial-gradient(ellipse at 70% 80%, var(--amber) 0%, transparent 50%);"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-display font-medium text-[var(--ink)] no-underline" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-none">
                        <x-app-logo-icon class="me-2 h-7 fill-current text-[var(--ink)]" />
                    </span>
                    {{ config('app.name', 'Laravel') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <div class="font-display text-2xl leading-snug font-medium text-[var(--ink)]" style="font-family: var(--font-display);">&ldquo;{{ trim($message) }}&rdquo;</div>
                        <footer><span class="font-body text-sm text-[var(--ink-muted)]" style="font-family: var(--font-body);">{{ trim($author) }}</span></footer>
                    </blockquote>
                </div>
            </div>

            {{-- Auth form panel --}}
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-display font-medium text-[var(--ink)] no-underline lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-none">
                            <x-app-logo-icon class="size-9 fill-current text-[var(--ink)] dark:text-[var(--ink)]" />
                        </span>
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
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
