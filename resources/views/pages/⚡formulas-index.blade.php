<?php

use App\Models\Formula;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Formulas")] #[Layout("layouts.app")] class extends Component {
    public string $search = "";

    public function delete(int $id): void
    {
        Formula::findOrFail($id)->delete();
    }

    public function clone(int $id): void
    {
        $original = Formula::with("materials")->findOrFail($id);

        $clone = Formula::create([
            "nama_formula" => $original->nama_formula . " (copy)",
            "deskripsi" => $original->deskripsi,
            "jenis_konsentrasi" => $original->jenis_konsentrasi,
            "volume_botol_ml" => $original->volume_botol_ml,
        ]);

        foreach ($original->materials as $mat) {
            $clone->materials()->attach($mat->id, [
                "persentase" => $mat->pivot->persentase,
                "gram" => $mat->pivot->gram,
                "note_posisi" => $mat->pivot->note_posisi,
            ]);
        }

        $this->redirectRoute("formulas.edit", $clone->id);
    }

    #[Computed]
    public function formulas()
    {
        $q = Formula::withCount("materials");

        if ($this->search) {
            $q->where(function ($q) {
                $q->where("nama_formula", "like", "%{$this->search}%")
                  ->orWhere("deskripsi", "like", "%{$this->search}%");
            });
        }

        return $q->orderByDesc("created_at")->get();
    }
};
?>

<div class="p-6 lg:p-8" x-data="{ deleteId: null }">
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Formulas</h1>
            <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ App\Models\Formula::count() }} formulas in library</p>
        </div>
        <a href="{{ route('formulas.create') }}" class="btn-amber text-sm no-underline">New Formula</a>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative max-w-sm">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Search formulas..."
                   class="w-full rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] pl-8 pr-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]" />
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--ink-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    @php $formulas = $this->formulas(); @endphp

    @if($formulas->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <p class="font-body text-sm text-[var(--ink-muted)]">No formulas found</p>
            <p class="mt-1 font-body text-xs text-[var(--ink-muted)] opacity-60">{{ $search ? 'Try a different search term' : 'Begin by compounding your first formula' }}</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-3 pr-4">Name</th>
                        <th class="micro-label py-3 pr-4">Type</th>
                        <th class="micro-label py-3 pr-4 text-right">Materials</th>
                        <th class="micro-label py-3 pr-4 text-right">Created</th>
                        <th class="micro-label py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulas as $formula)
                        <tr class="border-b border-[var(--border-soft)] text-[var(--ink)] hover:bg-[var(--paper)]">
                            <td class="py-3 pr-4">
                                <span class="font-medium">{{ $formula->nama_formula }}</span>
                                @if($formula->jenis_konsentrasi)
                                    <span class="ml-1.5 badge-amber text-[10px]">{{ $formula->jenis_konsentrasi->value }}</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">
                                @if($formula->volume_botol_ml)
                                    {{ $formula->volume_botol_ml }} ml
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--ink-muted)]">{{ $formula->materials_count }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--ink-muted)]">{{ $formula->created_at->format('d M Y') }}</td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <a href="{{ route('formulas.show', $formula->id) }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--ink)] hover:underline">View</a>
                                <span class="text-[var(--border-hair)] mx-1">/</span>
                                <a href="{{ route('formulas.edit', $formula->id) }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--amber)] hover:underline">Edit</a>
                                <span class="text-[var(--border-hair)] mx-1">/</span>
                                <button wire:click="clone({{ $formula->id }})" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--sage)] hover:underline">Clone</button>
                                <span class="text-[var(--border-hair)] mx-1">/</span>
                                <button @click="deleteId = {{ $formula->id }}" class="font-body text-xs text-[var(--ink-muted)] underline-offset-2 hover:text-[var(--terracotta)] hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Delete Confirmation --}}
        <div x-show="deleteId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" @click.away="deleteId = null">
            <div class="card max-w-sm mx-4 space-y-4">
                <p class="font-body text-sm text-[var(--ink)]">Delete this formula?</p>
                <p class="font-body text-xs text-[var(--ink-muted)]">This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button @click="deleteId = null" class="btn-ghost text-sm">Cancel</button>
                    <button wire:click="delete(deleteId)" @click="deleteId = null" class="btn-danger text-sm">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
