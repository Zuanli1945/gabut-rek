<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produks';

    protected $fillable = [
        'nama_produk', 'biaya_produksi_id', 'harga_jual', 'stock',
        'is_active', 'scent_family_tags', 'description_html', 'images',
        'is_pre_order', 'pre_order_quota', 'is_sample_available', 'sample_price', 'sample_volume_ml',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'decimal:2',
            'stock' => 'decimal:2',
            'is_active' => 'boolean',
            'scent_family_tags' => 'array',
            'images' => 'array',
            'is_pre_order' => 'boolean',
            'is_sample_available' => 'boolean',
            'sample_price' => 'decimal:2',
            'sample_volume_ml' => 'decimal:2',
        ];
    }

    public function biayaProduksi(): BelongsTo
    {
        return $this->belongsTo(BiayaProduksi::class, 'biaya_produksi_id');
    }

    public function formulas(): BelongsToMany
    {
        return $this->belongsToMany(Formula::class, 'formula_produk')->withPivot('jumlah_ml', 'persentase_komposisi');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'produk_id');
    }
}
