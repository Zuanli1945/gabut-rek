@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="VOC Atelier" {{ $attributes->merge(['class' => 'font-display']) }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-none bg-[var(--ink)] text-[var(--paper)]">
            <x-app-logo-icon class="size-5 fill-current" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="VOC Atelier" {{ $attributes->merge(['class' => 'font-display']) }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-none bg-[var(--ink)] text-[var(--paper)]">
            <x-app-logo-icon class="size-5 fill-current" />
        </x-slot>
    </flux:brand>
@endif
