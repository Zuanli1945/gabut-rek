<?php

use App\Models\Material;
use App\Models\Formula;
use App\Models\Produk;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Studio")] #[Layout("layouts.app")] class extends Component {
    #[Computed]
    public function statMaterialsCount(): int
    {
        return Material::count();
    }

    #[Computed]
    public function statFormulasCount(): int
    {
        return Formula::count();
    }

    #[Computed]
    public function statProductsCount(): int
    {
        return Produk::count();
    }

    #[Computed]
    public function statStockValue(): float
    {
        return Material::sum("harga_beli");
    }

    #[Computed]
    public function lowStockMaterials()
    {
        return Material::where("stock_saat_ini", ">", 0)
            ->where("stock_saat_ini", "<", 10)
            ->orderBy("stock_saat_ini")
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function outOfStockMaterials()
    {
        return Material::where("stock_saat_ini", "<=", 0)
            ->orderByDesc("harga_beli")
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function formulaNoteDistribution(): array
    {
        $top = 0;
        $mid = 0;
        $base = 0;
        $total = 0;

        Formula::with("materials")->each(function ($f) use (&$top, &$mid, &$base, &$total) {
            foreach ($f->materials as $m) {
                $total++;
                match ($m->pivot->note_posisi) {
                    "top" => $top++,
                    "mid" => $mid++,
                    "base" => $base++,
                    default => null,
                };
            }
        });

        return [
            "top" => $top,
            "mid" => $mid,
            "base" => $base,
            "total" => $total,
        ];
    }

    #[Computed]
    public function mostUsedMaterials()
    {
        return Material::withCount("formulas")
            ->orderByDesc("formulas_count")
            ->limit(5)
            ->get()
            ->filter(fn($m) => $m->formulas_count > 0);
    }

    #[Computed]
    public function recentActivities(): array
    {
        $activities = collect();

        Material::select(["id", "nama", "created_at"])
            ->orderByDesc("created_at")->limit(5)->each(
                fn($m) => $activities->push(["type" => "material", "name" => $m->nama, "time" => $m->created_at])
            );

        Formula::select(["id", "nama_formula", "created_at"])
            ->orderByDesc("created_at")->limit(5)->each(
                fn($f) => $activities->push(["type" => "formula", "name" => $f->nama_formula, "time" => $f->created_at])
            );

        Produk::select(["id", "nama_produk", "created_at"])
            ->orderByDesc("created_at")->limit(5)->each(
                fn($p) => $activities->push(["type" => "product", "name" => $p->nama_produk, "time" => $p->created_at])
            );

        return $activities->sortByDesc("time")->take(10)->values()->toArray();
    }

    public function goTo(string $route): void
    {
        $this->redirectRoute($route);
    }
};
?>

<div class="p-6 lg:p-8">
    <div class="mb-10">
        <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Studio</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Overview of your compounding workspace</p>
    </div>

    {{-- Stats --}}
    <div class="mb-10 grid gap-4 grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <p class="micro-label mb-1">Materials</p>
            <p class="font-display text-2xl font-medium text-[var(--ink)] tabular-nums">{{ $this->stat_materials_count }}</p>
        </div>
        <div class="card">
            <p class="micro-label mb-1">Formulas</p>
            <p class="font-display text-2xl font-medium text-[var(--ink)] tabular-nums">{{ $this->stat_formulas_count }}</p>
        </div>
        <div class="card">
            <p class="micro-label mb-1">Products</p>
            <p class="font-display text-2xl font-medium text-[var(--ink)] tabular-nums">{{ $this->stat_products_count }}</p>
        </div>
        <div class="card">
            <p class="micro-label mb-1">Total Invested</p>
            <p class="font-display text-2xl font-medium text-[var(--amber)] tabular-nums">Rp{{ number_format($this->stat_stock_value, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Stock Alerts --}}
        <div class="card lg:col-span-2">
            <p class="micro-label mb-4">Stock Alerts</p>

            @php $lowStock = $this->low_stock_materials; @endphp
            @php $outOfStock = $this->out_of_stock_materials; @endphp

            @if($lowStock->isEmpty() && $outOfStock->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <p class="font-body text-sm text-[var(--sage)]">All materials are well-stocked</p>
                </div>
            @else
                @foreach($outOfStock as $mat)
                    <div class="flex items-center justify-between py-2 border-t border-[var(--border-soft)] first:border-t-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="badge-terracotta text-[10px] shrink-0">Out</span>
                            <span class="font-body text-sm text-[var(--ink)] truncate">{{ $mat->nama }}</span>
                        </div>
                        <span class="font-body text-xs text-[var(--ink-muted)] shrink-0 ml-2">{{ $mat->tipe }}</span>
                    </div>
                @endforeach
                @foreach($lowStock as $mat)
                    <div class="flex items-center justify-between py-2 border-t border-[var(--border-soft)]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="badge-amber text-[10px] shrink-0">Low</span>
                            <span class="font-body text-sm text-[var(--ink)] truncate">{{ $mat->nama }}</span>
                        </div>
                        <span class="font-body text-xs text-[var(--ink-muted)] tabular-nums shrink-0 ml-2">{{ number_format($mat->stock_saat_ini, 1) }} {{ $mat->satuan }}</span>
                    </div>
                @endforeach
                <div class="mt-3 pt-3 border-t border-[var(--border-hair)]">
                    <a href="{{ route('materials.index') }}" class="font-body text-xs text-[var(--amber)] hover:underline">View all materials →</a>
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <p class="micro-label mb-4">Quick Actions</p>
            <div class="flex flex-col gap-3">
                <button type="button" class="btn-amber w-full text-sm" wire:click="goTo('materials.create')">New Material</button>
                <button type="button" class="btn-ghost w-full text-sm" wire:click="goTo('formulas.create')">New Formula</button>
                <button type="button" class="btn-ghost w-full text-sm" wire:click="goTo('products.create')">New Product</button>
            </div>
        </div>
    </div>

    {{-- Note Distribution + Most Used --}}
    @php $noteDist = $this->formula_note_distribution; @endphp
    @if($noteDist['total'] > 0)
        <div class="grid gap-6 lg:grid-cols-2 mt-6">
            <div class="card">
                <p class="micro-label mb-4">Note Pyramid Distribution</p>
                <div class="flex items-end gap-4">
                    @php $maxNote = max($noteDist['top'], $noteDist['mid'], $noteDist['base'], 1); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="font-body text-xs text-[var(--ink-muted)] tabular-nums">{{ $noteDist['top'] }}</span>
                        <div class="w-full bg-[var(--border-soft)]" style="height: 4px;">
                            <div class="h-full bg-amber-400" style="width: {{ ($noteDist['top'] / $maxNote) * 100 }}%"></div>
                        </div>
                        <span class="micro-label text-[10px]">Top</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="font-body text-xs text-[var(--ink-muted)] tabular-nums">{{ $noteDist['mid'] }}</span>
                        <div class="w-full bg-[var(--border-soft)]" style="height: 4px;">
                            <div class="h-full bg-[var(--amber)]" style="width: {{ ($noteDist['mid'] / $maxNote) * 100 }}%"></div>
                        </div>
                        <span class="micro-label text-[10px]">Mid</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="font-body text-xs text-[var(--ink-muted)] tabular-nums">{{ $noteDist['base'] }}</span>
                        <div class="w-full bg-[var(--border-soft)]" style="height: 4px;">
                            <div class="h-full bg-amber-800" style="width: {{ ($noteDist['base'] / $maxNote) * 100 }}%"></div>
                        </div>
                        <span class="micro-label text-[10px]">Base</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <p class="micro-label mb-4">Most Used Materials</p>
                @php $topMats = $this->most_used_materials; @endphp
                @if($topMats->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topMats as $mat)
                            <div class="flex items-center justify-between py-1">
                                <span class="font-body text-sm text-[var(--ink)]">{{ $mat->nama }}</span>
                                <span class="badge-amber text-[10px]">{{ $mat->formulas_count }} formulas</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="font-body text-xs text-[var(--ink-muted)]">No materials used in formulas yet</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Recent Activity --}}
    <div class="card mt-6">
        <p class="micro-label mb-4">Recent Activity</p>
        @php $activities = $this->recent_activities; @endphp
        @if(empty($activities))
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <p class="font-body text-sm text-[var(--ink-muted)]">No activity yet</p>
                <p class="mt-1 font-body text-xs text-[var(--ink-muted)] opacity-60">Begin by adding your first material</p>
            </div>
        @else
            <div class="flex flex-col">
                @foreach($activities as $i => $activity)
                    @php
                        $colorMap = ['material' => 'bg-[var(--sage)]', 'formula' => 'bg-[var(--amber)]', 'product' => 'bg-[var(--terracotta)]'];
                        $labelMap = ['material' => 'Material', 'formula' => 'Formula', 'product' => 'Product'];
                    @endphp
                    <div @class(["flex items-center gap-3 py-2.5", 'border-t border-[var(--border-soft)]' => $i > 0])>
                        <span @class(["h-1.5 w-1.5 shrink-0 rounded-none", $colorMap[$activity['type']]])></span>
                        <span class="min-w-0 flex-1">
                            <span class="font-body text-sm text-[var(--ink)]">{{ $activity['name'] }}</span>
                            <span class="micro-label ml-1.5">{{ $labelMap[$activity['type']] }}</span>
                        </span>
                        <span class="font-body text-xs text-[var(--ink-muted)] shrink-0">{{ $activity['time']->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
