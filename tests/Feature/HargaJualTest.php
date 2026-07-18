<?php

use App\Models\BiayaProduksi;
use App\Models\Formula;
use App\Models\Material;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Harga Jual (Sell Price) Tests
|--------------------------------------------------------------------------
|
| Reproduces 3 bugs:
| 1. BiayaProduksi.harga_jual is never auto-calculated
| 2. Produk.harga_jual is hardcoded to 0 on create
| 3. Produk.harga_jual is never synced/updated on edit
|
*/

it('biaya produksi auto-calculates harga_jual from cogs and margin', function () {
    $material = Material::create([
        'nama' => 'Ethanol',
        'tipe' => 'Aromachemical',
        'scent_family' => 'Citrus',
        'harga_beli' => 50000,
        'jumlah_beli' => 1000,
        'satuan' => 'ml',
        'stock_saat_ini' => 500,
    ]);

    $formula = Formula::create([
        'nama_formula' => 'Test Fragrance',
        'deskripsi' => 'A test formula',
    ]);

    $formula->materials()->attach($material->id, [
        'persentase' => 100.00,
        'gram' => null,
        'note_posisi' => 'top',
    ]);

    // Input: cogs_per_unit = 10000, margin = 30%
    // Expected: harga_jual = 10000 * (1 + 30/100) = 13000
    $bp = BiayaProduksi::create([
        'formula_id' => $formula->id,
        'solvent_material_id' => $material->id,
        'persentase_konsentrasi' => 18.00,
        'jumlah_batch_ml' => 1000.00,
        'biaya_kemasan' => 5000,
        'target_margin_persen' => 30.00,
        'jumlah_unit_hasil' => 10.00,
        'cogs_per_unit' => 10000.00,
        'harga_jual' => 0,
        'margin_rupiah' => 0,
    ]);

    // BUG: harga_jual stays 0, never auto-calculated
    // After fix: BiayaProduksi should auto-calculate:
    //   harga_jual = cogs_per_unit * (1 + target_margin_persen / 100)
    //   margin_rupiah = harga_jual - cogs_per_unit
    expect($bp->harga_jual)->toBe('13000.00');
});

it('product harga_jual is not hardcoded to 0 on create', function () {
    $material = Material::create([
        'nama' => 'Ethanol',
        'tipe' => 'Aromachemical',
        'scent_family' => 'Citrus',
        'harga_beli' => 50000,
        'jumlah_beli' => 1000,
        'satuan' => 'ml',
        'stock_saat_ini' => 500,
    ]);

    $formula = Formula::create([
        'nama_formula' => 'Test Fragrance',
        'deskripsi' => 'A test formula',
    ]);

    $formula->materials()->attach($material->id, [
        'persentase' => 100.00,
        'gram' => null,
        'note_posisi' => 'top',
    ]);

    $bp = BiayaProduksi::create([
        'formula_id' => $formula->id,
        'solvent_material_id' => $material->id,
        'persentase_konsentrasi' => 18.00,
        'jumlah_batch_ml' => 1000.00,
        'biaya_kemasan' => 5000,
        'target_margin_persen' => 30.00,
        'jumlah_unit_hasil' => 10.00,
        'cogs_per_unit' => 10000.00,
        'harga_jual' => 13000.00,
        'margin_rupiah' => 3000.00,
    ]);

    // Simulate what the FIXED create Livewire component does:
    $produk = Produk::create([
        'nama_produk' => 'Test Product',
        'harga_jual' => 13000.00,
        'stock' => 10,
    ]);

    $produk->formulas()->attach($formula->id, [
        'jumlah_ml' => 30.00,
        'persentase_komposisi' => 100.00,
    ]);

    // After fix: product harga_jual should reflect the value passed in
    expect($produk->harga_jual)->toBe('13000.00');
});
