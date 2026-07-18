<?php

use App\Enums\JenisKonsentrasi;
use App\Models\Formula;
use App\Models\Material;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Edit Formula")] #[Layout("layouts.app")] class extends Component {
    public int $formulaId;
    public string $nama_formula = "";
    public string $deskripsi = "";
    public ?string $jenis_konsentrasi = null;
    public ?int $volume_botol_ml = null;
    public string $materialsJson = "[]";
    public string $solventMaterialId = "";

    public array $materialOptions = [];
    public array $solventOptions = [];

    public function mount(int $id): void
    {
        $this->formulaId = $id;

        $formula = Formula::with("materials")->findOrFail($id);
        $this->nama_formula = $formula->nama_formula;
        $this->deskripsi = $formula->deskripsi ?? "";
        $this->jenis_konsentrasi = $formula->jenis_konsentrasi?->value;
        $this->volume_botol_ml = $formula->volume_botol_ml;
        $this->solventMaterialId = (string) ($formula->biayaProduksi?->solvent_material_id ?? '');

        $this->materialsJson = $formula->materials
            ->map(
                fn($m) => [
                    "material_id" => $m->id,
                    "persentase" => (float) $m->pivot->persentase,
                    "gram" => (float) ($m->pivot->gram ?? 0),
                    "note_posisi" => $m->pivot->note_posisi ?? "mid",
                ],
            )
            ->toJson();

        $this->solventOptions = Material::where('tipe', 'Aromachemical')
            ->orWhere('tipe', 'Essential Oil')
            ->get(['id', 'nama', 'satuan', 'harga_per_satuan'])
            ->toArray();

        $this->materialOptions = Material::with("subCategory")
            ->select([
                "id",
                "nama",
                "satuan",
                "harga_beli",
                "jumlah_beli",
            ])
            ->get()
            ->map(
                fn($m) => [
                    "id" => $m->id,
                    "nama" => $m->nama,
                    "sub_category" => $m->subCategory?->name ?? "—",
                    "satuan" => $m->satuan,
                    "harga_per_satuan" => $m->harga_per_satuan,
                ],
            )
            ->toArray();
    }

    public function save(): void
    {
        $this->validate([
            "nama_formula" => "required|string|max:255",
            "deskripsi" => "nullable|string|max:1000",
            "jenis_konsentrasi" => "nullable|string|max:255",
            "volume_botol_ml" => "nullable|integer|min:1",
        ]);

        $materials = json_decode($this->materialsJson, true) ?? [];

        if (empty($materials)) {
            $this->addError("materialsJson", "Add at least one material.");
            return;
        }

        foreach ($materials as $i => $mat) {
            if (
                empty($mat["material_id"]) ||
                !isset($mat["persentase"]) ||
                $mat["persentase"] <= 0
            ) {
                $this->addError(
                    "materialsJson",
                    "Row " .
                        ($i + 1) .
                        ": select a material and enter a valid percentage.",
                );
                return;
            }
        }

        $formula = Formula::findOrFail($this->formulaId);

        $formula->update([
            "nama_formula" => $this->nama_formula,
            "deskripsi" => $this->deskripsi,
            "jenis_konsentrasi" => $this->jenis_konsentrasi,
            "volume_botol_ml" => $this->volume_botol_ml,
        ]);

        $formula->materials()->detach();
        foreach ($materials as $mat) {
            $attach = [
                "persentase" => $mat["persentase"],
                "note_posisi" => $mat["note_posisi"] ?? "mid",
            ];
            if (! empty($mat["gram"]) && $mat["gram"] > 0) {
                $attach["gram"] = $mat["gram"];
            }
            $formula->materials()->attach($mat["material_id"], $attach);
        }

        $solventId = $this->solventMaterialId ? (int) $this->solventMaterialId : null;
        $formula->biayaProduksi()->firstOrCreate([])
            ->update(['solvent_material_id' => $solventId]);

        $this->redirectRoute("formulas.index");
    }
};
?>

<div class="p-6 lg:p-8"
     x-data="{
        materials: @js(json_decode($this->materialsJson, true)),
        materialOptions: @js($this->materialOptions),
        jenisKonsentrasi: @js($this->jenis_konsentrasi),
        volumeBotolMl: @js($this->volume_botol_ml),
        get totalPersentase() {
            return this.materials.reduce((s, m) => s + parseFloat(m.persentase || 0), 0);
        },
        get totalGram() {
            return this.materials.reduce((s, m) => s + parseFloat(m.gram || 0), 0);
        },
        get hppPerMl() {
            return this.materials.reduce((s, m) => {
                const opt = this.materialOptions.find(o => o.id == m.material_id);
                return s + (opt ? (parseFloat(m.persentase || 0) / 100) * opt.harga_per_satuan : 0);
            }, 0);
        },
        getHppFormatted() {
            return new Intl.NumberFormat('id-ID').format(Math.round(this.hppPerMl));
        },
        gramDerivedPct(mat) {
            if (!this.totalGram || !mat.gram) return null;
            return ((parseFloat(mat.gram || 0) / this.totalGram) * 100).toFixed(1);
        },
        hasBreakdown() {
            return this.materials.some(m => parseFloat(m.gram || 0) > 0) && this.totalGram > 0;
        },
        concentrationRanges: {
            'Parfum/Extrait': { min: 20, max: 30 },
            'EDP': { min: 15, max: 20 },
            'EDT': { min: 5, max: 15 },
            'Cologne/EDC': { min: 2, max: 5 }
        },
        get rekomendasi() {
            if (!this.jenisKonsentrasi || !this.volumeBotolMl) return null;
            const r = this.concentrationRanges[this.jenisKonsentrasi];
            if (!r) return null;
            const vol = parseFloat(this.volumeBotolMl);
            return {
                min: (vol * r.min / 100).toFixed(1),
                max: (vol * r.max / 100).toFixed(1),
                solventMin: (vol - vol * r.max / 100).toFixed(1),
                solventMax: (vol - vol * r.min / 100).toFixed(1)
            };
        },
        addMaterial() {
            this.materials.push({ material_id: '', persentase: 0, gram: 0, note_posisi: 'mid' });
        },
        removeMaterial(i) {
            this.materials.splice(i, 1);
        },
        getSelectedMaterial(id) {
            return this.materialOptions.find(o => o.id == id);
        },
        submitForm() {
            $wire.set('materialsJson', JSON.stringify(this.materials)).then(() => {
                $wire.call('save');
            });
        }
     }">

    <div class="mb-8">
        <a href="{{ route('formulas.index') }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:underline">Back to Formulas</a>
        <h1 class="mt-2 font-display text-3xl font-medium text-[var(--ink)]">Edit Formula</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Update formula composition and cost</p>
    </div>

    <form x-on:submit.prevent="submitForm()" class="max-w-2xl space-y-6">

        {{-- Basic Info --}}
        <div class="card space-y-4">
            <p class="micro-label mb-2">Formula Info</p>
            <div>
                <label class="micro-label mb-1 block">Formula Name</label>
                <input type="text" wire:model="nama_formula" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />
                @error('nama_formula') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="micro-label mb-1 block">Description</label>
                <textarea wire:model="deskripsi" rows="2" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]"></textarea>
                @error('deskripsi') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="micro-label mb-1 block">Concentration Type</label>
                    <select x-model="jenisKonsentrasi" wire:model="jenis_konsentrasi" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                        <option value="">Select...</option>
                        @foreach(\App\Enums\JenisKonsentrasi::cases() as $jk)
                            <option value="{{ $jk->value }}">{{ $jk->label() }} ({{ $jk->rangeMin() }}–{{ $jk->rangeMax() }}%)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="micro-label mb-1 block">Bottle Volume</label>
                    <div class="flex items-center gap-2">
                        <template x-for="preset in [30, 50, 100]" :key="preset">
                            <button type="button" @click="volumeBotolMl = preset; $wire.set('volume_botol_ml', preset)"
                                    class="px-2 py-1 rounded-[2px] border font-body text-xs tabular-nums transition-colors"
                                    :class="volumeBotolMl == preset ? 'border-[var(--amber)] bg-[var(--amber)] text-white' : 'border-[var(--border-hair)] bg-[var(--cream)] text-[var(--ink-muted)] hover:border-[var(--amber)]'"
                                    x-text="preset + ' ml'"></button>
                        </template>
                        <div class="relative flex-1">
                            <input type="number" x-model.number="volumeBotolMl" wire:model="volume_botol_ml" min="1" step="1"
                                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-1 pr-6 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="ml" />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 font-body text-xs text-[var(--ink-muted)]">ml</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Material Composition --}}
        <div class="card space-y-4">
            <div class="flex items-center justify-between">
                <p class="micro-label">Material Composition</p>
                <p class="micro-label" :class="totalPersentase > 100 ? 'text-[var(--terracotta)]' : totalPersentase === 100 ? 'text-[var(--sage)]' : ''">
                    Total: <span x-text="totalPersentase.toFixed(1)"></span>% <span x-show="totalPersentase === 100">✓</span>
                </p>
            </div>

            @error('materialsJson') <p class="text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror

            <div class="hidden sm:grid sm:grid-cols-12 gap-2">
                <div class="col-span-4 micro-label">Material</div>
                <div class="col-span-2 micro-label">%</div>
                <div class="col-span-2 micro-label">Gram</div>
                <div class="col-span-2 micro-label">Position</div>
                <div class="col-span-1 micro-label text-right">Cost/ml</div>
                <div class="col-span-1"></div>
            </div>

            <template x-for="(mat, i) in materials" :key="i">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                    <div class="sm:col-span-4">
                        <select x-model="mat.material_id" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                            <option value="">Select material...</option>
                            @foreach($materialOptions as $opt)
                                <option value="{{ $opt['id'] }}">{{ $opt['nama'] }} ({{ $opt['sub_category'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="relative">
                            <input type="number" x-model.number="mat.persentase" min="0" max="100" step="0.1"
                                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 pr-6 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 font-body text-xs text-[var(--ink-muted)]">%</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="relative">
                            <input type="number" x-model.number="mat.gram" min="0" step="0.1"
                                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 pr-6 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 font-body text-xs text-[var(--ink-muted)]">g</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <select x-model="mat.note_posisi" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                            <option value="top">Top</option>
                            <option value="mid">Mid</option>
                            <option value="base">Base</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1 sm:text-right">
                        <span class="font-body text-xs text-[var(--ink-muted)] tabular-nums"
                              x-text="getSelectedMaterial(mat.material_id) ? 'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(getSelectedMaterial(mat.material_id).harga_per_satuan)) : '—'"></span>
                    </div>
                    <div class="sm:col-span-1 sm:text-right">
                        <button type="button" @click="removeMaterial(i)" class="font-body text-xs text-[var(--ink-muted)] hover:text-[var(--terracotta)]">✕</button>
                    </div>
                </div>
            </template>

            <button type="button" @click="addMaterial()" class="btn-ghost w-full text-sm border-dashed">+ Add Material</button>
        </div>

        {{-- Material Breakdown (gram-based) --}}
        <div class="card space-y-3" x-show="hasBreakdown()" x-cloak>
            <p class="micro-label">Material Breakdown (by Gram)</p>
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-2 pr-2">Material</th>
                        <th class="micro-label py-2 pr-2 text-right">Manual %</th>
                        <th class="micro-label py-2 pr-2 text-right">Gram %</th>
                        <th class="micro-label py-2 pr-2 text-right">Diff</th>
                        <th class="micro-label py-2 text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(mat, i) in materials.filter(m => parseFloat(m.gram || 0) > 0)" :key="i">
                        <tr class="border-b border-[var(--border-soft)]">
                            <td class="py-2 pr-2" x-text="getSelectedMaterial(mat.material_id)?.nama || '—'"></td>
                            <td class="py-2 pr-2 text-right tabular-nums" x-text="parseFloat(mat.persentase || 0).toFixed(1) + '%'"></td>
                            <td class="py-2 pr-2 text-right tabular-nums" x-text="gramDerivedPct(mat) + '%'"></td>
                            <td class="py-2 pr-2 text-right tabular-nums"
                                :class="Math.abs(parseFloat(gramDerivedPct(mat)) - parseFloat(mat.persentase || 0)) > 1 ? 'text-[var(--terracotta)]' : 'text-[var(--sage)]'"
                                x-text="(Math.abs(parseFloat(gramDerivedPct(mat)) - parseFloat(mat.persentase || 0))).toFixed(1) + '%'"></td>
                            <td class="py-2 text-right">
                                <span x-show="Math.abs(parseFloat(gramDerivedPct(mat)) - parseFloat(mat.persentase || 0)) <= 1" class="text-[var(--sage)]">✓</span>
                                <span x-show="Math.abs(parseFloat(gramDerivedPct(mat)) - parseFloat(mat.persentase || 0)) > 1" class="text-[var(--terracotta)]">⚠</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="font-body text-xs text-[var(--ink-muted)]">Total gram: <span class="tabular-nums" x-text="totalGram.toFixed(1)"></span> g</p>
        </div>

        {{-- Concentration Guide --}}
        <div class="card space-y-2" x-show="rekomendasi" x-cloak>
            <p class="micro-label">Concentration Guide</p>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-[2px] bg-[var(--amber)] px-2 py-0.5 font-body text-xs font-medium text-white" x-text="jenisKonsentrasi"></span>
                <span class="font-body text-sm text-[var(--ink)]">
                    in <span class="tabular-nums" x-text="volumeBotolMl"></span> ml bottle →
                    Concentrate: <span class="tabular-nums font-medium" x-text="rekomendasi?.min"></span>–<span class="tabular-nums font-medium" x-text="rekomendasi?.max"></span> ml,
                    Solvent: <span class="tabular-nums" x-text="rekomendasi?.solventMin"></span>–<span class="tabular-nums" x-text="rekomendasi?.solventMax"></span> ml
                </span>
            </div>
            <p class="font-body text-xs text-[var(--ink-muted)]">Guide only — adjust concentrate volume as needed for your formula.</p>
        </div>

        {{-- Note Pyramid Visual Guide --}}
        <div class="card">
            <p class="micro-label mb-4">Fragrance Note Pyramid</p>
            <div class="flex flex-col items-center">
                <svg viewBox="0 0 280 220" class="w-full max-w-[240px] h-auto">
                    <defs>
                        <linearGradient id="topGrad2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#fbbf24" stop-opacity="0.9" />
                            <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.4" />
                        </linearGradient>
                        <linearGradient id="midGrad2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#d97706" stop-opacity="0.7" />
                            <stop offset="100%" stop-color="#b45309" stop-opacity="0.4" />
                        </linearGradient>
                        <linearGradient id="baseGrad2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#92400e" stop-opacity="0.6" />
                            <stop offset="100%" stop-color="#78350f" stop-opacity="0.5" />
                        </linearGradient>
                    </defs>
                    <polyline points="140,5 20,200 260,200 140,5" fill="none" stroke="var(--border-hair)" stroke-width="0.5" stroke-dasharray="3,3" />
                    <polygon points="140,10 75,90 205,90" fill="url(#topGrad2)" />
                    <text x="140" y="62" text-anchor="middle" font-family="var(--font-body)" font-size="10" font-weight="600" fill="#78350f" letter-spacing="2">TOP</text>
                    <text x="140" y="76" text-anchor="middle" font-family="var(--font-body)" font-size="8" fill="#78350f" opacity="0.8">15–25%</text>
                    <polygon points="75,90 45,150 235,150 205,90" fill="url(#midGrad2)" />
                    <text x="140" y="128" text-anchor="middle" font-family="var(--font-body)" font-size="10" font-weight="600" fill="#fef3c7" letter-spacing="2">MIDDLE</text>
                    <text x="140" y="142" text-anchor="middle" font-family="var(--font-body)" font-size="8" fill="#fef3c7" opacity="0.8">30–50%</text>
                    <polygon points="45,150 20,205 260,205 235,150" fill="url(#baseGrad2)" />
                    <text x="140" y="186" text-anchor="middle" font-family="var(--font-body)" font-size="10" font-weight="600" fill="#fef3c7" letter-spacing="2">BASE</text>
                    <text x="140" y="200" text-anchor="middle" font-family="var(--font-body)" font-size="8" fill="#fef3c7" opacity="0.8">30–50%</text>
                </svg>
                <div class="mt-4 flex items-center gap-6 text-center">
                    <div>
                        <span class="inline-block w-3 h-3 rounded-sm bg-amber-300 align-middle mr-1.5"></span>
                        <span class="micro-label text-[10px]">Top — Citrus, herbal</span>
                    </div>
                    <div>
                        <span class="inline-block w-3 h-3 rounded-sm bg-amber-600 align-middle mr-1.5"></span>
                        <span class="micro-label text-[10px]">Mid — Floral, spice</span>
                    </div>
                    <div>
                        <span class="inline-block w-3 h-3 rounded-sm bg-amber-900 align-middle mr-1.5"></span>
                        <span class="micro-label text-[10px]">Base — Wood, musk</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cost Summary --}}
        <div class="card" x-show="materials.length > 0">
            <p class="micro-label mb-3">Cost Summary (Concentrate)</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="micro-label mb-0.5">Total Composition</p>
                    <p class="font-display text-lg font-medium tabular-nums"
                       :class="totalPersentase > 100 ? 'text-[var(--terracotta)]' : 'text-[var(--ink)]'"
                       x-text="totalPersentase.toFixed(1) + '%'"></p>
                </div>
                <div>
                    <p class="micro-label mb-0.5">HPP per ml (Concentrate)</p>
                    <p class="font-display text-lg font-medium text-[var(--amber)] tabular-nums">
                        Rp<span x-text="getHppFormatted()"></span>/ml
                    </p>
                </div>
            </div>
            <p class="mt-3 font-body text-xs text-[var(--ink-muted)]" x-show="totalPersentase !== 100 && materials.length > 0">
                Composition should total 100% for accurate costing.
            </p>
        </div>

        {{-- Solvent Material --}}
        <div class="card space-y-2">
            <p class="micro-label">Solvent Material</p>
            <select wire:model="solventMaterialId" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                <option value="">Select solvent...</option>
                @foreach($solventOptions as $sol)
                    <option value="{{ $sol['id'] }}">{{ $sol['nama'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('formulas.index') }}" class="btn-ghost text-sm no-underline">Cancel</a>
            <button type="submit" class="btn-amber text-sm">Update Formula</button>
        </div>
    </form>
</div>
