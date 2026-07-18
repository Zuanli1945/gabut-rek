<?php

use App\Models\Material;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Materials")] #[Layout("layouts.app")] class extends Component {
    public string $search = "";
    public string $typeFilter = "";

    public function delete(int $id): void
    {
        Material::findOrFail($id)->delete();
    }

    #[Computed]
    public function materials()
    {
        $q = Material::with("subCategory");

        if ($this->search) {
            $q->where(function ($q) {
                $q->where("nama", "like", "%{$this->search}%")
                  ->orWhere("scent_family", "like", "%{$this->search}%");
            });
        }

        if ($this->typeFilter) {
            $q->where("tipe", $this->typeFilter);
        }

        return $q->get();
    }

    public function types(): array
    {
        return \App\Enums\MaterialType::cases();
    }
};
?>

<div class="p-6 lg:p-8" x-data="{ deleteId: null }">
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Materials</h1>
            <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ App\Models\Material::count() }} materials in library</p>
        </div>
        <a href="{{ route('materials.create') }}" class="btn-amber text-sm no-underline">New Material</a>
    </div>

    {{-- Search & Filter --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search materials..."
                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] pl-8 pr-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--ink-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <select wire:model.live="typeFilter" class="rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
            <option value="">All Types</option>
            @foreach($this->types() as $t)
                <option value="{{ $t->value }}">{{ $t->value }}</option>
            @endforeach
        </select>
    </div>

    @php $materials = $this->materials(); @endphp

    @if($materials->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <p class="font-body text-sm text-[var(--ink-muted)]">No materials found</p>
            <p class="mt-1 font-body text-xs text-[var(--ink-muted)] opacity-60">{{ $search ? 'Try a different search term' : 'Begin by adding your first raw material' }}</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-3 pr-4">Name</th>
                        <th class="micro-label py-3 pr-4">Sub-Category</th>
                        <th class="micro-label py-3 pr-4">Type</th>
                        <th class="micro-label py-3 pr-4">Scent Family</th>
                        <th class="micro-label py-3 pr-4 text-right">Stock</th>
                        <th class="micro-label py-3 pr-4 text-right">Cost/Unit</th>
                        <th class="micro-label py-3 pr-4 text-center">Status</th>
                        <th class="micro-label py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $material)
                        @php
                            $lowStock = $material->stock_saat_ini > 0 && $material->stock_saat_ini < 10;
                            $outOfStock = $material->stock_saat_ini <= 0;
                        @endphp
                        <tr class="border-b border-[var(--border-soft)] text-[var(--ink)] hover:bg-[var(--paper)]">
                            <td class="py-3 pr-4 font-medium">{{ $material->nama }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $material->subCategory?->name ?? '—' }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $material->tipe }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $material->scent_family }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums {{ $lowStock ? 'text-[var(--amber)]' : ($outOfStock ? 'text-[var(--terracotta)]' : '') }}">{{ number_format($material->stock_saat_ini, 1) }} {{ $material->satuan }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--amber)]">Rp{{ number_format($material->harga_per_satuan, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-center">
                                @if($outOfStock)
                                    <span class="badge-terracotta">Out</span>
                                @elseif($lowStock)
                                    <span class="badge-amber">Low</span>
                                @else
                                    <span class="badge-sage">OK</span>
                                @endif
                            </td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <a href="{{ route('materials.edit', $material->id) }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--amber)] hover:underline">Edit</a>
                                <span class="text-[var(--border-hair)] mx-1">/</span>
                                <button @click="deleteId = {{ $material->id }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--terracotta)] hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Confirmation Modal --}}
        <div x-show="deleteId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" @click.away="deleteId = null">
            <div class="card max-w-sm mx-4 space-y-4">
                <p class="font-body text-sm text-[var(--ink)]">Delete material?</p>
                <p class="font-body text-xs text-[var(--ink-muted)]">This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button @click="deleteId = null" class="btn-ghost text-sm">Cancel</button>
                    <button wire:click="delete(deleteId)" @click="deleteId = null" class="btn-danger text-sm">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
