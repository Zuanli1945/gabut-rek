<?php

use App\Models\BiayaProduksi;
use App\Models\Formula;
use App\Models\Material;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Formula Detail")] #[Layout("layouts.app")] class extends Component {
    public Formula $formula;

    public function mount(int $id): void
    {
        $this->formula = Formula::with("materials.subCategory")->findOrFail($id);
    }
};
?>

<div class="p-6 lg:p-8"
     x-data="{
        batchMl: {{ $formula->volume_botol_ml ?? 50 }},
        solventMaterialId: '',
        solventPct: {{ $formula->jenis_konsentrasi ? (100 - $formula->jenis_konsentrasi->rangeMax()) : 80 }},
        packagingCost: 5000,
        targetMargin: 50,
        get materials() { return @js($formula->materials->map(fn($m) => [
            'id' => $m->id,
            'nama' => $m->nama,
            'pct' => (float) $m->pivot->persentase,
            'gram' => (float) ($m->pivot->gram ?? 0),
            'harga_per_satuan' => $m->harga_per_satuan,
            'satuan' => $m->satuan,
            'note_posisi' => $m->pivot->note_posisi,
        ])->toArray()) },
        solventOptions: @js(Material::where('tipe', 'Aromachemical')->orWhere('tipe', 'Essential Oil')->get(['id', 'nama', 'satuan', 'harga_beli', 'jumlah_beli'])->map(fn($m) => [
            'id' => $m->id,
            'nama' => $m->nama,
            'harga_per_satuan' => $m->harga_per_satuan,
        ])->toArray()),
        get concentrateMl() {
            return this.materials.reduce((s, m) => s + (m.pct / 100) * parseFloat(this.batchMl || 0) * (1 - this.solventPct / 100) / 100, 0);
        },
        get solventMl() {
            return (this.solventPct / 100) * parseFloat(this.batchMl || 0);
        },
        get concentrateCost() {
            return this.materials.reduce((s, m) => {
                const costPerMl = (m.pct / 100) * (1 - this.solventPct / 100) * parseFloat(this.batchMl || 0) / 100 * m.harga_per_satuan;
                return s + costPerMl;
            }, 0);
        },
        get solventCost() {
            const sol = this.solventOptions.find(s => s.id == this.solventMaterialId);
            return sol ? this.solventMl * sol.harga_per_satuan : 0;
        },
        get totalCost() {
            return this.concentrateCost + this.solventCost + parseFloat(this.packagingCost || 0);
        },
        get cogsPerUnit() {
            return this.totalCost;
        },
        get sellPrice() {
            return this.cogsPerUnit * (1 + parseFloat(this.targetMargin || 0) / 100);
        },
        get profitPerUnit() {
            return this.sellPrice - this.cogsPerUnit;
        },
        formatRp(val) {
            return new Intl.NumberFormat('id-ID').format(Math.round(val));
        }
     }">

    <div class="mb-8">
        <a href="{{ route('formulas.index') }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:underline">Back to Formulas</a>
        <div class="mt-2 flex items-end justify-between">
            <div>
                <h1 class="font-display text-3xl font-medium text-[var(--ink)]">{{ $formula->nama_formula }}</h1>
                <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ $formula->deskripsi ?? 'No description' }}</p>
            </div>
            <a href="{{ route('formulas.edit', $formula->id) }}" class="btn-ghost text-sm no-underline">Edit</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">

        {{-- Left: Formula Info + Materials --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Concentration & Volume Info --}}
            @if($formula->jenis_konsentrasi || $formula->volume_botol_ml)
                <div class="card space-y-2">
                    <p class="micro-label">Concentration</p>
                    <div class="flex flex-wrap items-center gap-3">
                        @if($formula->jenis_konsentrasi)
                            <span class="badge-amber">{{ $formula->jenis_konsentrasi->label() }} ({{ $formula->jenis_konsentrasi->rangeMin() }}–{{ $formula->jenis_konsentrasi->rangeMax() }}%)</span>
                        @endif
                        @if($formula->volume_botol_ml)
                            <span class="font-body text-sm text-[var(--ink)]">Bottle: {{ $formula->volume_botol_ml }} ml</span>
                        @endif
                    </div>
                    @php $rek = $formula->rekomendasiKonsentratMl(); @endphp
                    @if($rek)
                        <p class="font-body text-sm text-[var(--ink-muted)]">
                            Concentrate: {{ $rek['min'] }}–{{ $rek['max'] }} ml · Solvent: {{ $formula->volume_botol_ml - $rek['max'] }}–{{ $formula->volume_botol_ml - $rek['min'] }} ml
                        </p>
                    @endif
                </div>
            @endif

            {{-- Material Breakdown --}}
            @php
                $materials = $formula->materials->sortByDesc(fn($m) => (float) $m->pivot->gram);
                $totalGram = $materials->sum(fn($m) => (float) ($m->pivot->gram ?? 0));
            @endphp
            <div class="card space-y-3">
                <div class="flex items-center justify-between">
                    <p class="micro-label">Material Breakdown</p>
                    @if($totalGram > 0)
                        <p class="micro-label">Total: {{ number_format($totalGram, 1) }} g</p>
                    @endif
                </div>

                <div class="table-wrap">
                    <table class="w-full text-left font-body text-sm">
                        <thead>
                            <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                                <th class="micro-label py-2 pr-2">#</th>
                                <th class="micro-label py-2 pr-2">Material</th>
                                <th class="micro-label py-2 pr-2">Note</th>
                                <th class="micro-label py-2 pr-2 text-right">%</th>
                                <th class="micro-label py-2 pr-2 text-right">Gram</th>
                                <th class="micro-label py-2 text-right">Diff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $i => $m)
                                @php
                                    $gram = (float) ($m->pivot->gram ?? 0);
                                    $pct = (float) $m->pivot->persentase;
                                    $gramPct = $totalGram > 0 && $gram > 0 ? round(($gram / $totalGram) * 100, 1) : null;
                                    $diff = $gramPct !== null ? abs($gramPct - $pct) : null;
                                @endphp
                                <tr class="border-b border-[var(--border-soft)]">
                                    <td class="py-2 pr-2 text-[var(--ink-muted)]">{{ $i + 1 }}</td>
                                    <td class="py-2 pr-2 font-medium">{{ $m->nama }}</td>
                                    <td class="py-2 pr-2">
                                        <span class="note-dot {{ $m->pivot->note_posisi }}"></span>
                                    </td>
                                    <td class="py-2 pr-2 text-right tabular-nums">{{ number_format($pct, 1) }}%</td>
                                    <td class="py-2 pr-2 text-right tabular-nums">{{ $gram > 0 ? number_format($gram, 1) . ' g' : '—' }}</td>
                                    <td class="py-2 text-right tabular-nums
                                        @if($diff !== null && $diff > 1) text-[var(--terracotta)]
                                        @elseif($diff !== null) text-[var(--sage)]
                                        @endif">
                                        @if($diff !== null)
                                            {{ number_format($diff, 1) }}%
                                            @if($diff <= 1) ✓ @else ⚠ @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($totalGram <= 0)
                    <p class="font-body text-xs text-[var(--ink-muted)]">No gram data entered. Edit this formula to add gram values for breakdown analysis.</p>
                @endif
            </div>
        </div>

        {{-- Right: Batch Costing Calculator --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card space-y-4">
                <p class="micro-label">Batch Costing Calculator</p>

                <div>
                    <label class="micro-label mb-1 block">Batch Volume (ml)</label>
                    <input type="number" x-model.number="batchMl" min="1" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" />
                </div>

                <div>
                    <label class="micro-label mb-1 block">Solvent (%)</label>
                    <input type="number" x-model.number="solventPct" min="0" max="100" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" />
                </div>

                <div>
                    <label class="micro-label mb-1 block">Solvent Material</label>
                    <select x-model="solventMaterialId" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                        <option value="">Select solvent...</option>
                        <template x-for="sol in solventOptions" :key="sol.id">
                            <option :value="sol.id" x-text="sol.nama + ' (Rp' + formatRp(sol.harga_per_satuan) + '/' + sol.satuan + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="micro-label mb-1 block">Packaging Cost (Rp)</label>
                    <input type="number" x-model.number="packagingCost" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" />
                </div>

                <div>
                    <label class="micro-label mb-1 block">Target Margin (%)</label>
                    <input type="number" x-model.number="targetMargin" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" />
                </div>

                <div class="border-t border-[var(--border-hair)] pt-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-body text-xs text-[var(--ink-muted)]">Concentrate Cost</span>
                        <span class="font-body text-sm text-[var(--ink)] tabular-nums" x-text="'Rp' + formatRp(concentrateCost)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-body text-xs text-[var(--ink-muted)]">Solvent Cost</span>
                        <span class="font-body text-sm text-[var(--ink)] tabular-nums" x-text="'Rp' + formatRp(solventCost)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-body text-xs text-[var(--ink-muted)]">Packaging</span>
                        <span class="font-body text-sm text-[var(--ink)] tabular-nums" x-text="'Rp' + formatRp(packagingCost)"></span>
                    </div>
                    <div class="flex justify-between border-t border-[var(--border-soft)] pt-2">
                        <span class="font-body text-xs font-medium text-[var(--ink)]">COGS / Unit</span>
                        <span class="font-display text-lg font-medium text-[var(--amber)] tabular-nums" x-text="'Rp' + formatRp(cogsPerUnit)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-body text-xs text-[var(--ink-muted)]">Sell Price</span>
                        <span class="font-body text-sm text-[var(--ink)] tabular-nums" x-text="'Rp' + formatRp(sellPrice)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-body text-xs font-medium" :class="profitPerUnit > 0 ? 'text-[var(--sage)]' : 'text-[var(--terracotta)]'">Profit / Unit</span>
                        <span class="font-body text-sm tabular-nums" :class="profitPerUnit > 0 ? 'text-[var(--sage)]' : 'text-[var(--terracotta)]'" x-text="'Rp' + formatRp(profitPerUnit)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
