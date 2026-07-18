<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'sub_category_id', 'nama', 'tipe', 'scent_family',
        'harga_beli', 'jumlah_beli', 'satuan', 'stock_saat_ini',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'jumlah_beli' => 'decimal:2',
            'stock_saat_ini' => 'decimal:2',
        ];
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function getHargaPerSatuanAttribute(): float
    {
        return $this->jumlah_beli > 0
            ? round($this->harga_beli / $this->jumlah_beli, 2)
            : 0;
    }

    public function formulas(): BelongsToMany
    {
        return $this->belongsToMany(Formula::class)
            ->using(FormulaMaterial::class)
            ->withPivot('persentase', 'gram', 'note_posisi');
    }

    public function biayaProduksi(): HasMany
    {
        return $this->hasMany(BiayaProduksi::class, 'solvent_material_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
