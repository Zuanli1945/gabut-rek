<?php

use App\Models\Produk;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Products")] #[Layout("layouts.app")] class extends Component {
    public string $search = "";

    public function delete(int $id): void
    {
        Produk::findOrFail($id)->delete();
    }

    #[Computed]
    public function products()
    {
        $q = Produk::with("formulas");

        if ($this->search) {
            $q->where("nama_produk", "like", "%{$this->search}%");
        }

        return $q->orderByDesc("created_at")->get();
    }
};
?>

<div class="p-6 lg:p-8" x-data="{ deleteId: null }">
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Products</h1>
            <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ App\Models\Produk::count() }} products in catalog</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn-amber text-sm no-underline">New Product</a>
    </div>

    <div class="mb-6">
        <div class="relative max-w-sm">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search products..."
                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] pl-8 pr-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--ink-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    @php $products = $this->products(); @endphp

    @if($products->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <p class="font-body text-sm text-[var(--ink-muted)]">No products found</p>
            <p class="mt-1 font-body text-xs text-[var(--ink-muted)] opacity-60">{{ $search ? 'Try a different search term' : 'Create a product with formulas' }}</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-3 pr-4">Name</th>
                        <th class="micro-label py-3 pr-4">Formula</th>
                        <th class="micro-label py-3 pr-4 text-right">Sell Price</th>
                        <th class="micro-label py-3 pr-4 text-right">Stock</th>
                        <th class="micro-label py-3 pr-4 text-center">Status</th>
                        <th class="micro-label py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php $outOfStock = $product->stock <= 0; @endphp
                        <tr class="border-b border-[var(--border-soft)] text-[var(--ink)] hover:bg-[var(--paper)]">
                            <td class="py-3 pr-4 font-medium">{{ $product->nama_produk }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)] max-w-[200px] truncate">
                                @if($product->formulas->isNotEmpty())
                                    {{ $product->formulas->pluck('nama_formula')->join(', ') }}
                                @else
                                    <span class="text-[var(--terracotta)]">No formula</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--amber)]">Rp{{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums {{ $outOfStock ? 'text-[var(--terracotta)]' : '' }}">{{ number_format($product->stock, 1) }}</td>
                            <td class="py-3 pr-4 text-center">
                                @if($outOfStock)
                                    <span class="badge-terracotta">Out</span>
                                @else
                                    <span class="badge-sage">In Stock</span>
                                @endif
                            </td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <a href="{{ route('products.edit', $product->id) }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--amber)] hover:underline">Edit</a>
                                <span class="text-[var(--border-hair)] mx-1">/</span>
                                <button @click="deleteId = {{ $product->id }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--terracotta)] hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div x-show="deleteId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" @click.away="deleteId = null">
            <div class="card max-w-sm mx-4 space-y-4">
                <p class="font-body text-sm text-[var(--ink)]">Delete this product?</p>
                <p class="font-body text-xs text-[var(--ink-muted)]">This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button @click="deleteId = null" class="btn-ghost text-sm">Cancel</button>
                    <button wire:click="delete(deleteId)" @click="deleteId = null" class="btn-danger text-sm">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
