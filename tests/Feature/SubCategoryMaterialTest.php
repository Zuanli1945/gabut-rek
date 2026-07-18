<?php

use App\Enums\MaterialType;
use App\Models\Material;
use App\Models\SubCategory;
use Database\Seeders\SubCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubCategorySeeder::class);
});

use function Pest\Laravel\get;

it('creates a material with type and sub-category', function () {
    $sub = SubCategory::where('type', 'aromachemical')->first();

    $material = Material::create([
        'nama' => 'Test Material',
        'sub_category_id' => $sub->id,
        'tipe' => 'Aromachemical',
        'scent_family' => 'Citrus',
        'harga_beli' => 100000,
        'jumlah_beli' => 100,
        'satuan' => 'ml',
        'stock_saat_ini' => 50,
    ]);

    expect($material->subCategory)->not->toBeNull();
    expect($material->subCategory->name)->toBe($sub->name);
    expect($material->tipe)->toBe('Aromachemical');
});

it('edits material sub-category', function () {
    $sub1 = SubCategory::where('type', 'essential_oil')->first();
    $sub2 = SubCategory::where('type', 'absolute')->first();

    $material = Material::create([
        'nama' => 'Changeable',
        'sub_category_id' => $sub1->id,
        'tipe' => 'Essential Oil',
        'scent_family' => 'Floral',
        'harga_beli' => 50000,
        'jumlah_beli' => 50,
        'satuan' => 'gram',
        'stock_saat_ini' => 25,
    ]);

    expect($material->subCategory->type)->toBe('essential_oil');

    $material->update([
        'sub_category_id' => $sub2->id,
        'tipe' => 'Absolute',
    ]);

    expect($material->fresh()->subCategory->type)->toBe('absolute');
});

it('creates custom sub-category on the fly', function () {
    $custom = SubCategory::create([
        'type' => 'accord',
        'name' => 'Test Accord Custom',
        'is_custom' => true,
    ]);

    expect($custom->is_custom)->toBeTrue();

    $material = Material::create([
        'nama' => 'Custom SubCat',
        'sub_category_id' => $custom->id,
        'tipe' => 'Accord',
        'scent_family' => 'Woody',
        'harga_beli' => 25000,
        'jumlah_beli' => 10,
        'satuan' => 'ml',
        'stock_saat_ini' => 5,
    ]);

    expect($material->subCategory->name)->toBe('Test Accord Custom');
});

it('sub-categories are filtered by type', function () {
    $aromachemicals = SubCategory::where('type', 'aromachemical')->get();
    $absolutes = SubCategory::where('type', 'absolute')->get();

    expect($aromachemicals->count())->toBeGreaterThan(0);
    expect($absolutes->count())->toBeGreaterThan(0);

    // No overlap between types
    $aroTypes = $aromachemicals->pluck('type')->unique();
    $absTypes = $absolutes->pluck('type')->unique();
    expect($aroTypes->count())->toBe(1);
    expect($aroTypes->first())->toBe('aromachemical');
    expect($absTypes->first())->toBe('absolute');
});

it('material can have null sub_category_id', function () {
    $material = Material::create([
        'nama' => 'No SubCat',
        'sub_category_id' => null,
        'tipe' => 'Aromachemical',
        'scent_family' => 'Citrus',
        'harga_beli' => 10000,
        'jumlah_beli' => 10,
        'satuan' => 'ml',
        'stock_saat_ini' => 5,
    ]);

    expect($material->subCategory)->toBeNull();
});

it('index page shows sub-category name', function () {
    $this->actingAs(\App\Models\User::factory()->create());

    $sub = SubCategory::where('type', 'essential_oil')->first();
    Material::create([
        'nama' => 'Lavender Oil',
        'sub_category_id' => $sub->id,
        'tipe' => 'Essential Oil',
        'scent_family' => 'Herbal',
        'harga_beli' => 30000,
        'jumlah_beli' => 50,
        'satuan' => 'ml',
        'stock_saat_ini' => 10,
    ]);

    $response = get(route('materials.index'));

    $response->assertOk();
    $response->assertSee('Lavender Oil');
    $response->assertSee($sub->name);
});
