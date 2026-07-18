<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiayaProduksi extends Model
{
    protected $fillable = [
        'formula_id', 'solvent_material_id',
        'persentase_konsentrasi', 'jumlah_batch_ml',
        'biaya_kemasan', 'target_margin_persen', 'jumlah_unit_hasil',
        'cogs_per_unit', 'harga_jual', 'margin_rupiah',
    ];

    protected function casts(): array
    {
        return [
            'persentase_konsentrasi' => 'decimal:2',
            'jumlah_batch_ml' => 'decimal:2',
            'biaya_kemasan' => 'decimal:2',
            'target_margin_persen' => 'decimal:2',
            'jumlah_unit_hasil' => 'decimal:2',
            'cogs_per_unit' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'margin_rupiah' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BiayaProduksi $bp) {
            $cogs = (float) $bp->cogs_per_unit;
            $margin = (float) $bp->target_margin_persen;
            $bp->harga_jual = round($cogs * (1 + $margin / 100), 2);
            $bp->margin_rupiah = round($bp->harga_jual - $cogs, 2);
        });
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function solvent(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'solvent_material_id');
    }

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'biaya_produksi_id');
    }
}
