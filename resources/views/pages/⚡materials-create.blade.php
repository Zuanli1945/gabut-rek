<?php

use App\Enums\MaterialType;
use App\Models\Material;
use App\Models\SubCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("New Material")] #[Layout("layouts.app")] class extends Component {
    public string $nama = "";
    public string $tipe = "Aromachemical";
    public ?int $subCategoryId = null;
    public string $subCategorySearch = "";
    public ?string $subCategoryName = null;
    public string $scent_family = "";
    public float $harga_beli = 0;
    public float $jumlah_beli = 0;
    public string $satuan = "ml";
    public float $stock_saat_ini = 0;

    public array $filteredSubCategories = [];

    public function mount(): void
    {
        $this->filterSubCategories();
    }

    public function updatedTipe(): void
    {
        $this->clearSubCategory();
        $this->filterSubCategories();
    }

    public function updatedSubCategorySearch(): void
    {
        $this->filterSubCategories();
    }

    public function filterSubCategories(): void
    {
        $slug = MaterialType::slug($this->tipe);
        if (!$slug) {
            $this->filteredSubCategories = [];
            return;
        }

        $query = SubCategory::where("type", $slug);
        if ($this->subCategorySearch) {
            $query->where("name", "like", "%" . $this->subCategorySearch . "%");
        }
        $this->filteredSubCategories = $query->limit(10)->get(["id", "name"])->toArray();
    }

    public function selectSubCategory(int $id): void
    {
        $sc = SubCategory::findOrFail($id);
        $this->subCategoryId = $sc->id;
        $this->subCategoryName = $sc->name;
        $this->subCategorySearch = "";
        $this->filteredSubCategories = [];
    }

    public function selectNewSubCategory(): void
    {
        $sc = SubCategory::create([
            "type" => MaterialType::slug($this->tipe),
            "name" => $this->subCategorySearch,
            "is_custom" => true,
        ]);
        $this->selectSubCategory($sc->id);
    }

    public function clearSubCategory(): void
    {
        $this->subCategoryId = null;
        $this->subCategoryName = null;
        $this->subCategorySearch = "";
        $this->filteredSubCategories = [];
    }

    protected function rules(): array
    {
        return [
            "nama" => "required|string|max:255",
            "subCategoryId" => "required|exists:sub_categories,id",
            "tipe" => "required|in:Aromachemical,Essential Oil,Absolute,Accord",
            "scent_family" => "required|string|max:255",
            "harga_beli" => "required|numeric|min:0",
            "jumlah_beli" => "required|numeric|min:0.01",
            "satuan" => "required|in:ml,gram",
            "stock_saat_ini" => "required|numeric|min:0",
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        Material::create([
            "nama" => $validated["nama"],
            "sub_category_id" => $validated["subCategoryId"],
            "tipe" => $validated["tipe"],
            "scent_family" => $validated["scent_family"],
            "harga_beli" => $validated["harga_beli"],
            "jumlah_beli" => $validated["jumlah_beli"],
            "satuan" => $validated["satuan"],
            "stock_saat_ini" => $validated["stock_saat_ini"],
        ]);

        $this->redirectRoute("materials.index");
    }
};
?>

<div class="p-6 lg:p-8">
    <div class="mb-8">
        <a href="{{ route('materials.index') }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:underline">Back to Materials</a>
        <h1 class="mt-2 font-display text-3xl font-medium text-[var(--ink)]">New Material</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Add a raw material to your library</p>
    </div>

    <form wire:submit="save" class="card max-w-xl space-y-6">
        <div>
            <label class="micro-label mb-1 block">Material Name</label>
            <input type="text" wire:model="nama" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" placeholder="e.g. Yuzu Essential Oil" />
            @error('nama') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            {{-- Type --}}
            <div>
                <label class="micro-label mb-1 block">Type</label>
                <select wire:model.live="tipe" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                    <option value="Aromachemical">Aromachemical</option>
                    <option value="Essential Oil">Essential Oil</option>
                    <option value="Absolute">Absolute</option>
                    <option value="Accord">Accord</option>
                </select>
            </div>

            {{-- Sub-Category --}}
            <div>
                <label class="micro-label mb-1 block">Sub-Category</label>

                @if($subCategoryId && !$subCategorySearch)
                    {{-- Selected pill --}}
                    <div class="flex items-center gap-2 rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2">
                        <span class="font-body text-sm text-[var(--ink)]">{{ $subCategoryName }}</span>
                        <button type="button" wire:click="clearSubCategory" class="ml-auto font-body text-lg leading-none text-[var(--ink-muted)] hover:text-[var(--terracotta)]">&times;</button>
                    </div>
                @else
                    {{-- Search combobox --}}
                    <div x-data="{ open: false }" @click-away="open = false" class="relative">
                        <input type="text" wire:model.live.debounce.150ms="subCategorySearch"
                               @focus="open = true"
                               @keydown.escape="open = false"
                               placeholder="Search sub-category..."
                               class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />

                        @if(strlen($subCategorySearch) > 0)
                            <div x-show="open" class="absolute left-0 right-0 z-20 mt-1 max-h-48 overflow-auto rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] shadow-sm">
                                @forelse($filteredSubCategories as $sc)
                                    <button type="button" wire:click="selectSubCategory({{ $sc['id'] }})"
                                            @click="open = false"
                                            class="w-full px-3 py-2 text-left font-body text-sm text-[var(--ink)] hover:bg-[var(--paper)]">
                                        {{ $sc['name'] }}
                                    </button>
                                @empty
                                    <button type="button" wire:click="selectNewSubCategory"
                                            @click="open = false"
                                            class="w-full px-3 py-2 text-left font-body text-sm text-[var(--amber)] hover:bg-[var(--paper)]">
                                        + Add "{{ $subCategorySearch }}"
                                    </button>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif

                @error('subCategoryId') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="micro-label mb-1 block">Scent Family</label>
            <input type="text" wire:model="scent_family" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" placeholder="e.g. Citrus, Floral, Woody" />
            @error('scent_family') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="micro-label mb-1 block">Purchase Price (Rp)</label>
                <input type="number" wire:model="harga_beli" step="0.01" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                @error('harga_beli') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="micro-label mb-1 block">Quantity Bought</label>
                <input type="number" wire:model="jumlah_beli" step="0.01" min="0.01" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
                @error('jumlah_beli') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="micro-label mb-1 block">Unit</label>
                <select wire:model="satuan" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
                    <option>ml</option>
                    <option>gram</option>
                </select>
            </div>
        </div>

        <div>
            <label class="micro-label mb-1 block">Current Stock</label>
            <input type="number" wire:model="stock_saat_ini" step="0.01" min="0" class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)] tabular-nums" placeholder="0" />
            @error('stock_saat_ini') <p class="mt-1 text-xs text-[var(--terracotta)]">{{ $message }}</p> @enderror
        </div>

        @if($harga_beli > 0 && $jumlah_beli > 0)
            <div class="border-t border-[var(--border-soft)] pt-4">
                <p class="micro-label mb-1">Cost Per Unit</p>
                <p class="font-display text-xl font-medium text-[var(--amber)] tabular-nums">Rp{{ number_format(round($harga_beli / $jumlah_beli, 2), 0, ',', '.') }}/{{ $satuan }}</p>
            </div>
        @endif

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('materials.index') }}" class="btn-ghost text-sm no-underline">Cancel</a>
            <button type="submit" class="btn-amber text-sm">Save Material</button>
        </div>
    </form>
</div>
