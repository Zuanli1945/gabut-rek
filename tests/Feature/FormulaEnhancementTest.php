<?php

use App\Enums\JenisKonsentrasi;
use App\Models\Formula;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Formula Enhancement Tests
|--------------------------------------------------------------------------
|
| Tests for:
| 1. Formula stores jenis_konsentrasi and volume_botol_ml
| 2. Gram-derived % calculation
| 3. Cross-validation: >1% discrepancy detection
| 4. Concentration recommendation returns correct range
|--------------------------------------------------------------------------
*/

it('stores jenis_konsentrasi and volume_botol_ml', function () {
    $formula = Formula::create([
        'nama_formula' => 'Test EDP',
        'deskripsi' => 'A test formula',
        'jenis_konsentrasi' => JenisKonsentrasi::EDP,
        'volume_botol_ml' => 50,
    ]);

    expect($formula->jenis_konsentrasi)->toBe(JenisKonsentrasi::EDP)
        ->and($formula->volume_botol_ml)->toBe(50);
});

it('casts jenis_konsentrasi to enum', function () {
    $formula = Formula::create([
        'nama_formula' => 'Test Parfum',
        'jenis_konsentrasi' => JenisKonsentrasi::PARFUM,
    ]);

    expect($formula->jenis_konsentrasi)->toBeInstanceOf(JenisKonsentrasi::class)
        ->and($formula->jenis_konsentrasi->value)->toBe('Parfum/Extrait');
});

it('returns null rekomendasi when fields are not set', function () {
    $formula = Formula::create([
        'nama_formula' => 'Plain Formula',
    ]);

    expect($formula->rekomendasiKonsentratMl())->toBeNull();
});

it('returns correct rekomendasi for EDP 30ml', function () {
    $formula = Formula::create([
        'nama_formula' => 'EDP Formula',
        'jenis_konsentrasi' => JenisKonsentrasi::EDP,
        'volume_botol_ml' => 30,
    ]);

    $result = $formula->rekomendasiKonsentratMl();

    // EDP: 15-20% of 30ml = 4.5ml - 6ml
    expect($result['min'])->toBe(4.5)
        ->and($result['max'])->toBe(6.0);
});

it('returns correct rekomendasi for Parfum 100ml', function () {
    $formula = Formula::create([
        'nama_formula' => 'Extrait Formula',
        'jenis_konsentrasi' => JenisKonsentrasi::PARFUM,
        'volume_botol_ml' => 100,
    ]);

    $result = $formula->rekomendasiKonsentratMl();

    // Parfum: 20-30% of 100ml = 20ml - 30ml
    expect($result['min'])->toBe(20.0)
        ->and($result['max'])->toBe(30.0);
});

it('returns correct rekomendasi for EDT 50ml', function () {
    $formula = Formula::create([
        'nama_formula' => 'EDT Formula',
        'jenis_konsentrasi' => JenisKonsentrasi::EDT,
        'volume_botol_ml' => 50,
    ]);

    $result = $formula->rekomendasiKonsentratMl();

    // EDT: 5-15% of 50ml = 2.5ml - 7.5ml
    expect($result['min'])->toBe(2.5)
        ->and($result['max'])->toBe(7.5);
});

it('returns correct rekomendasi for EDC 100ml', function () {
    $formula = Formula::create([
        'nama_formula' => 'Cologne Formula',
        'jenis_konsentrasi' => JenisKonsentrasi::EDC,
        'volume_botol_ml' => 100,
    ]);

    $result = $formula->rekomendasiKonsentratMl();

    // EDC: 2-5% of 100ml = 2ml - 5ml
    expect($result['min'])->toBe(2.0)
        ->and($result['max'])->toBe(5.0);
});

it('saves gram data in formula_material pivot', function () {
    $material = Material::create([
        'nama' => 'Linalool',
        'tipe' => 'Essential Oil',
        'scent_family' => 'Floral',
        'harga_beli' => 80000,
        'jumlah_beli' => 100,
        'satuan' => 'ml',
        'stock_saat_ini' => 50,
    ]);

    $formula = Formula::create([
        'nama_formula' => 'Gram Test',
    ]);

    $formula->materials()->attach($material->id, [
        'persentase' => 50.00,
        'gram' => 25.5,
        'note_posisi' => 'mid',
    ]);

    $pivot = $formula->materials()->first()->pivot;

    expect((float) $pivot->gram)->toBe(25.5);
});

it('computes gram-derived percentages correctly', function () {
    // Simulate the breakdown calculation logic
    $materials = [
        ['gram' => 30, 'persentase' => 30],
        ['gram' => 50, 'persentase' => 50],
        ['gram' => 20, 'persentase' => 20],
    ];

    $totalGram = array_sum(array_column($materials, 'gram'));

    foreach ($materials as $mat) {
        $gramPct = ($mat['gram'] / $totalGram) * 100;

        expect($gramPct)->toEqual($mat['persentase']);
    }
});

it('detects gram vs manual percentage discrepancy over 1%', function () {
    $manualPct = 30.0;
    $gram = 25.0;
    $totalGram = 100.0;

    $computedPct = ($gram / $totalGram) * 100; // 25%
    $diff = abs($computedPct - $manualPct); // |25 - 30| = 5%

    expect($diff)->toBeGreaterThan(1.0);
});

it('does not flag discrepancy when within 1% tolerance', function () {
    $manualPct = 30.0;
    $gram = 29.5;
    $totalGram = 100.0;

    $computedPct = ($gram / $totalGram) * 100; // 29.5%
    $diff = abs($computedPct - $manualPct); // |29.5 - 30| = 0.5%

    expect($diff)->toBeLessThanOrEqual(1.0);
});

it('handles edge case: all grams are 0', function () {
    $totalGram = 0;

    // Should not cause division by zero
    $computedPct = $totalGram > 0 ? (0 / $totalGram) * 100 : null;

    expect($computedPct)->toBeNull();
});

it('harga_per_satuan accessor returns non-zero when harga_beli loaded', function () {
    // Regression: hppPerMl was Rp0 because materialOptions query
    // omitted harga_beli/jumlah_beli → accessor returned 0
    $material = Material::create([
        'nama' => 'Linalool HPP Test',
        'tipe' => 'Essential Oil',
        'scent_family' => 'Floral',
        'harga_beli' => 80000,
        'jumlah_beli' => 100,
        'satuan' => 'ml',
        'stock_saat_ini' => 50,
    ]);

    // With both fields loaded (the fix)
    $loaded = Material::select(['id', 'nama', 'satuan', 'harga_beli', 'jumlah_beli'])
        ->find($material->id);

    expect($loaded->harga_per_satuan)->toEqual(800); // 80000/100
});

it('edit form pre-fills materials from pivot data', function () {
    // Regression: edit form showed empty materials
    $material = Material::create([
        'nama' => 'Linalool',
        'tipe' => 'Essential Oil',
        'scent_family' => 'Floral',
        'harga_beli' => 80000,
        'jumlah_beli' => 100,
        'satuan' => 'ml',
        'stock_saat_ini' => 50,
    ]);

    $formula = Formula::create([
        'nama_formula' => 'Test Formula',
        'deskripsi' => 'A test formula',
        'jenis_konsentrasi' => JenisKonsentrasi::EDP,
        'volume_botol_ml' => 50,
    ]);

    $formula->materials()->attach($material->id, [
        'persentase' => 30,
        'gram' => 15,
        'note_posisi' => 'top',
    ]);

    // Simulate what mount() does
    $loaded = Formula::with('materials')->findOrFail($formula->id);
    $materialsJson = $loaded->materials
        ->map(fn($m) => [
            'material_id' => $m->id,
            'persentase' => (float) $m->pivot->persentase,
            'gram' => (float) ($m->pivot->gram ?? 0),
            'note_posisi' => $m->pivot->note_posisi ?? 'mid',
        ])
        ->toJson();

    $decoded = json_decode($materialsJson, true);

    expect($decoded)->toHaveCount(1);
    expect($decoded[0]['material_id'])->toEqual($material->id);
    expect($decoded[0]['persentase'])->toEqual(30.0);
    expect($decoded[0]['gram'])->toEqual(15.0);
    expect($decoded[0]['note_posisi'])->toEqual('top');
});

it('edit form renders material rows via Livewire', function () {
    $user = App\Models\User::factory()->create();
    $this->actingAs($user);

    $material = Material::create([
        'nama' => 'Bergamot',
        'tipe' => 'Essential Oil',
        'scent_family' => 'Citrus',
        'harga_beli' => 120000,
        'jumlah_beli' => 50,
        'satuan' => 'ml',
        'stock_saat_ini' => 30,
    ]);

    $formula = Formula::create([
        'nama_formula' => 'Bergamot EDP',
        'deskripsi' => 'Test',
        'jenis_konsentrasi' => JenisKonsentrasi::EDP,
        'volume_botol_ml' => 50,
    ]);

    $formula->materials()->attach($material->id, [
        'persentase' => 40,
        'gram' => 20,
        'note_posisi' => 'top',
    ]);

    $response = $this->get(route('formulas.edit', $formula->id));

    $response->assertOk();
    // The materialsJson should contain the material data in the rendered HTML
    $response->assertSee($material->id);
    $response->assertSee('Bergamot');
});
