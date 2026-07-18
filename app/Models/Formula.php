<?php

namespace App\Models;

use App\Enums\JenisKonsentrasi;
use App\Models\BiayaProduksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Formula extends Model
{
    protected $fillable = [
        "nama_formula",
        "deskripsi",
        "jenis_konsentrasi",
        "volume_botol_ml",
    ];

    protected function casts(): array
    {
        return [
            "jenis_konsentrasi" => JenisKonsentrasi::class,
        ];
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)
            ->using(FormulaMaterial::class)
            ->withPivot("persentase", "gram", "note_posisi");
    }

    public function biayaProduksi(): HasOne
    {
        return $this->hasOne(BiayaProduksi::class);
    }

    public function solventMaterial(): ?Material
    {
        return $this->biayaProduksi?->solvent;
    }

    public function produks(): BelongsToMany
    {
        return $this->belongsToMany(Produk::class)->withPivot(
            "jumlah_ml",
            "persentase_komposisi",
        );
    }

    /**
     * Rekomendasi volume konsentrat (ml) berdasarkan jenis konsentrasi & volume botol.
     * @return array{min: float, max: float}|null
     */
    public function rekomendasiKonsentratMl(): ?array
    {
        if (! $this->jenis_konsentrasi || ! $this->volume_botol_ml) {
            return null;
        }

        $vol = (float) $this->volume_botol_ml;

        return [
            'min' => round($vol * $this->jenis_konsentrasi->rangeMin() / 100, 1),
            'max' => round($vol * $this->jenis_konsentrasi->rangeMax() / 100, 1),
        ];
    }
}
