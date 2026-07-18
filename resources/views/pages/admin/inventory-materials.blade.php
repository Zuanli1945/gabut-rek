<?php

use App\Models\Material;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $search = '';
    public string $status = '';

    #[Computed]
    public function materials()
    {
        $q = Material::with('subCategory', 'supplier');

        if ($this->search) {
            $q->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('scent_family', 'like', "%{$this->search}%");
            });
        }

        return $q->orderByDesc('stock_saat_ini')->get();
    }
};

?>

<div class="p-6 lg:p-8">
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Inventory — Materials</h1>
            <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ App\Models\Material::count() }} materials tracked</p>
        </div>
        <a href="{{ route('materials.create') }}" class="btn-amber text-sm no-underline">New Material</a>
    </div>

    <div class="mb-6">
        <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search materials..."
               class="w-full max-w-sm rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />
    </div>

    @php $materials = $this->materials; @endphp

    @if($materials->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <p class="font-body text-sm text-[var(--ink-muted)]">No materials found</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-3 pr-4">Name</th>
                        <th class="micro-label py-3 pr-4">Type</th>
                        <th class="micro-label py-3 pr-4">Supplier</th>
                        <th class="micro-label py-3 pr-4 text-right">Stock</th>
                        <th class="micro-label py-3 pr-4 text-right">Cost/Unit</th>
                        <th class="micro-label py-3 pr-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $m)
                        @php
                            $isLow = $m->stock_saat_ini > 0 && $m->stock_saat_ini < $m->threshold_stock;
                            $isOut = $m->stock_saat_ini <= 0;
                        @endphp
                        <tr class="border-b border-[var(--border-soft)] text-[var(--ink)] hover:bg-[var(--paper)]">
                            <td class="py-3 pr-4 font-medium">{{ $m->nama }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $m->tipe }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $m->supplier?->name ?? '—' }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums {{ $isLow ? 'text-[var(--amber)]' : ($isOut ? 'text-[var(--terracotta)]' : '') }}">{{ number_format($m->stock_saat_ini, 1) }} {{ $m->satuan }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--amber)]">Rp{{ number_format($m->harga_per_satuan, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-center">
                                @if($isOut) <span class="badge-terracotta">Out</span>
                                @elseif($isLow) <span class="badge-amber">Low</span>
                                @else <span class="badge-sage">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
