@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <div class="font-display text-2xl font-medium text-[var(--ink)]" style="font-family: var(--font-display);">{{ $title }}</div>
    <div class="mt-1 font-body text-sm text-[var(--ink-muted)]" style="font-family: var(--font-body);">{{ $description }}</div>
</div>
