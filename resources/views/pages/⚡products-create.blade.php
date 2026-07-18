<?php

use App\Models\Formula;
use App\Models\Produk;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("New Product")] #[Layout("layouts.app")] class extends Component {
    public string $nama_produk = "";
    public string $formulaJson = "[]";
    public float $stock = 0;
    public float $harga_jual = 0;

    public array $formulaOptions = [];

    public function mount(): void
    {
        $this->formulaOptions = Formula::select(["id", "nama_formula"])
            ->withCount("materials")
            ->get()
            ->map(
                fn($f) => [
                    "id" => $f->id,
                    "nama_formula" => $f->nama_formula,
                    "materials_count" => $f->materials_count,
                ],
            )
            ->toArray();
    }

    public function save(): void
    {
        $this->validate([
            "nama_produk" => "required|string|max:255",
            "stock" => "required|numeric|min:0",
            "harga_jual" => "required|numeric|min:0",
        ]);

        $formulas = json_decode($this->formulaJson, true) ?? [];

        $produk = Produk::create([
            "nama_produk" => $this->nama_produk,
            "harga_jual" => $this->harga_jual,
            "stock" => $this->stock,
        ]);

        foreach ($formulas as $f) {
            if (!empty($f["formula_id"])) {
                $produk->formulas()->attach($f["formula_id"], [
                    "jumlah_ml" => $f["jumlah_ml"] ?? 0,
                    "persentase_komposisi" => $f["persentase_komposisi"] ?? 0,
                ]);
            }
        }

        $this->redirectRoute("products.index");
    }
};
?>

<div class="p-6 lg:p-8"
     x-data="{
        formulas: @js(json_decode($this->formulaJson, true)),
        formulaOptions: @js($this->formulaOptions),
        get totalKomposisi() {
            return this.formulas.reduce((s, f) => s + parseFloat(f.persentase_komposisi || 0), 0);
        },
        addFormula() {
            this.formulas.push({ formula_id: '', jumlah_ml: 0, persentase_komposisi: 0 });
        },
        removeFormula(i) {
            this.formulas.splice(i, 1);
        },
        getSelectedFormula(id) {
            return this.formulaOptions.find(o => o.id == id);
        },
        submitForm() {
            $wire.set('formulaJson', JSON.stringify(this.formulas)).then(() => {
                $wire.call('save');
            });
        }
     }">

    <div class="mb-8">
        <a href="{{ route('products.index') }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:underline">Back to Products</a>
        <h1 class="mt-2 font-display text-3xl font-medium text-[var(--ink)]">New Product</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Add a product and assign formulas</p>
    </div>

    <form x-on:submit.prevent="submitForm()" class="max-w-2xl space-y-6">

        <div class="card space-y-4">
            <p class="micro-label mb-2">Product Info</p>
            <div>
                <label class="micro-label mb-1 block">Product Name</label>
                <input type="text" wire:model="nama_produk" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" placeholder="e.g. Yuzu Matcha Milk — 30ml" />
                @error('nama_produk') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="micro-label mb-1 block">Stock</label>
                <input type="number" wire:model="stock" step="0.01" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                @error('stock') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="micro-label mb-1 block">Sell Price (Rp)</label>
                <input type="number" wire:model="harga_jual" step="1" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                @error('harga_jual') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card space-y-4">
            <div class="flex items-center justify-between">
                <p class="micro-label">Formula Composition</p>
                <p class="micro-label" :class="totalKomposisi > 100 ? 'text-[var(--terracotta)]' : totalKomposisi === 100 ? 'text-[var(--sage)]' : ''">
                    Total: <span x-text="totalKomposisi.toFixed(1)"></span>% <span x-show="totalKomposisi === 100">✓</span>
                </p>
            </div>

            <template x-for="(f, i) in formulas" :key="i">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                    <div class="sm:col-span-5">
                        <select x-model="f.formula_id" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                            <option value="">Select formula...</option>
                            <template x-for="opt in formulaOptions" :key="opt.id">
                                <option :value="opt.id" x-text="opt.nama_formula + ' (' + opt.materials_count + ' mat)'"></option>
                            </template>
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="relative">
                            <input type="number" x-model.number="f.jumlah_ml" min="0" step="0.1"
                                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 pr-8 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 font-body text-xs text-[var(--ink-muted)]">ml</span>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="relative">
                            <input type="number" x-model.number="f.persentase_komposisi" min="0" max="100" step="0.1"
                                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 pr-6 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 font-body text-xs text-[var(--ink-muted)]">%</span>
                        </div>
                    </div>
                    <div class="sm:col-span-1 sm:text-right">
                        <button type="button" @click="removeFormula(i)" class="font-body text-xs text-[var(--ink-muted)] hover:text-[var(--terracotta)]">✕</button>
                    </div>
                </div>
            </template>

            <button type="button" @click="addFormula()" class="btn-ghost w-full text-sm border-dashed">+ Add Formula</button>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('products.index') }}" class="btn-ghost text-sm no-underline">Cancel</a>
            <button type="submit" class="btn-amber text-sm">Save Product</button>
        </div>
    </form>
</div>
