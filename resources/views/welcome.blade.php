<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} — VOC Atelier</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="bg-[var(--cream)] text-[var(--ink)] antialiased">
        {{-- Navigation --}}
        <header class="fixed top-0 left-0 right-0 z-50 bg-[var(--cream)]/90 backdrop-blur-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="font-display text-lg font-medium tracking-[0.05em] text-[var(--ink)] no-underline uppercase">VOC Atelier</a>
                <nav class="flex items-center gap-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-body text-sm text-[var(--ink-muted)] no-underline transition-colors hover:text-[var(--ink)]">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-body text-sm text-[var(--ink-muted)] no-underline transition-colors hover:text-[var(--ink)]">Log in</a>
                        <a href="{{ route('register') }}" class="btn-amber text-sm no-underline">Begin</a>
                    @endauth
                </nav>
            </div>
        </header>

        {{-- Hero --}}
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col items-center justify-center px-6 lg:px-8">
            <div class="flex w-full flex-col items-center text-center">
                {{-- Abstract decorative element --}}
                <div class="mb-12 flex items-center gap-3 opacity-40">
                    <span class="block h-px w-12 bg-[var(--amber)]"></span>
                    <span class="block h-1.5 w-1.5 rounded-none bg-[var(--amber)]"></span>
                    <span class="block h-px w-12 bg-[var(--amber)]"></span>
                </div>

                <h1 class="font-display text-5xl font-medium leading-tight tracking-tight text-[var(--ink)] lg:text-7xl">
                    Compose<br>
                    <span class="text-[var(--amber)]">your signature</span>
                </h1>

                <p class="mt-6 max-w-md font-body text-base leading-relaxed text-[var(--ink-muted)]">
                    A perfumer's workspace for formulating, costing, and cataloguing bespoke fragrances. Material-led. Precision-crafted.
                </p>

                <div class="mt-10 flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-amber no-underline">Enter studio</a>
                    @else
                        <a href="{{ route('register') }}" class="btn-amber no-underline">Begin compounding</a>
                        <a href="{{ route('login') }}" class="btn-ghost no-underline">Returning</a>
                    @endauth
                </div>
            </div>

            {{-- Bottom accent --}}
            <div class="mt-32 flex flex-col items-center gap-4 text-center">
                <span class="block h-px w-8 bg-[var(--border-hair)]"></span>
                <p class="font-body text-xs tracking-[0.15em] text-[var(--ink-muted)] uppercase">Est. 2025</p>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist
        @fluxScripts
    </body>
</html>
