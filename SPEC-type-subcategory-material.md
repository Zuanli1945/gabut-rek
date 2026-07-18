# Spec: Type-based Sub-Category Material

## Objective

Replace the multi-category system (Top Note/Blender/Middle/Modifier/Base Note checkboxes) with a **Type → Sub-Category** hierarchy. Each material has exactly one Type and one Sub-Category under that Type. The old `categories` + `material_category` tables are dropped entirely.

## Assumptions (confirmed)

1. Material = 1 Type = 1 Sub-Category (belongsTo, NOT many-to-many)
2. Safe to DROP `categories` + `material_category` (no real user data — feature is brand new)
3. Existing `tipe` column on `materials` stays — now stores `Aromachemical | Essential Oil | Absolute | Accord`
4. New `sub_categories` table has `type` column to group items by parent type
5. Sub-category seed data = standard industry perfume ingredient names
6. Scent Family field stays as-is (independent)

## Tech Stack

Laravel 13, Livewire, Flux UI, Blade, SQLite/MySQL, Pest

## Commands

```
Build:  npm run build
Test:   php artisan test
Migrate: php artisan migrate:fresh --seed
```

## Project Structure

```
database/migrations/
  ..._create_sub_categories_table.php          → NEW
  ..._add_sub_category_id_to_materials.php     → NEW
  ..._drop_categories_tables.php               → NEW (drop categories + material_category)
database/seeders/
  SubCategorySeeder.php                        → NEW (replaces CategorySeeder)
  DatabaseSeeder.php                           → MODIFIED (calls SubCategorySeeder instead)
app/Models/
  SubCategory.php                              → NEW (replaces Category.php)
  Category.php                                 → DELETED
  Material.php                                 → MODIFIED (belongsTo SubCategory, drop categories())
app/Enums/
  MaterialType.php                             → NEW (replaces inline string validation)
resources/views/pages/
  ⚡materials-create.blade.php                 → MODIFIED (Type dropdown + searchable SubCategory combobox)
  ⚡materials-edit.blade.php                   → MODIFIED (same as create, pre-filled)
  ⚡materials-index.blade.php                  → MODIFIED (show SubCategory instead of categories)
  ⚡formulas-create.blade.php                  → MODIFIED (display sub_category->name in option text — NOTE: Alpine x-for bug in select pre-selection is a known separate issue, NOT addressed in this spec)
  ⚡formulas-edit.blade.php                    → MODIFIED (same)
tests/Feature/
  SubCategoryMaterialTest.php                  → NEW
```

## Data Model

### New table: `sub_categories`

| Column     | Type     | Notes                                       |
|------------|----------|---------------------------------------------|
| id         | bigint   | PK                                          |
| type       | string   | Parent type: aromachemical / essential_oil / absolute / accord |
| name       | string   | Sub-category name (unique per type)         |
| is_custom  | boolean  | false = seeded by system, true = user-added |
| timestamps |          |                                             |

Composite unique: `(type, name)` — prevents duplicates per type.

### Modified table: `materials`

| Added/Changed | Type    | Notes                                    |
|---------------|---------|------------------------------------------|
| sub_category_id | bigint | FK → sub_categories, nullable (until migrated) |

After migration: `tipe` column can optionally be dropped (redundant with sub_category.type), but keeping it is simpler and avoids a complex migration. **Decision: keep `tipe` column** — the form stores Type in `tipe` and Sub-Category ID in `sub_category_id`. They stay in sync.

### Enums: `MaterialType`

```php
enum MaterialType: string
{
    case AROMACHEMICAL = 'Aromachemical';
    case ESSENTIAL_OIL = 'Essential Oil';
    case ABSOLUTE      = 'Absolute';
    case ACCORD        = 'Accord';
}
```

The `type` column in `sub_categories` stores the **lowercase/snake_case** variant: `aromachemical`, `essential_oil`, `absolute`, `accord`.

### Relation

```php
// Material
public function subCategory(): BelongsTo
{
    return $this->belongsTo(SubCategory::class);
}

// SubCategory
public function materials(): HasMany
{
    return $this->hasMany(Material::class);
}
```

## Seed Data

### Aromachemical sub-categories (common industry names)
Hedione, Hedione HC, Iso E Super, Iso Gamma Super, Verdox, Romandolide, Galaxolide, Habanolide, cashmeran, Vertofix, Cedramber, Ambermore, Ambroxan, Ambrox Super, Super, Dihydromyrcenol, Linalool, Linalyl Acetate, Limonene, Geraniol, Citral, Nerolidol, Benzyl Acetate, Hexyl Cinnamal, Methyl Ionone, Alpha-Isomethyl Ionone, Ionone Beta, Orris Concrete, Orris Butter, Coumarin, Vanillin, Ethyl Vanillin, Heliotropin, Maltol, Ethyl Maltol, Musks (Galaxolide, Habanolide, etc.), Calone, Norlimbanol, Cedarwood Virginia, Sandalore, Javanol, Timbersilk, Clearwood, Patchouli Heart, Keora

### Essential Oil sub-categories
Lavender, Bergamot, Lemon, Sweet Orange, Grapefruit, Ylang Ylang, Geranium, Rosemary, Peppermint, Eucalyptus, Tea Tree, Clary Sage, Cedarwood Atlas, Cedarwood Himalayan, Sandalwood Mysore, Sandalwood Australian, Vetiver Haiti, Patchouli, Frankincense, Myrrh, Petitgrain, Neroli, Lemon Verbena, Lemongrass, Basil, Juniper Berry, Cypress, Cinnamon Bark, Clove Bud, Cardamom, Ginger, Black Pepper, Litsea Cubeba

### Absolute sub-categories
Rose Absolute, Jasmine Absolute (Sambac), Jasmine Absolute (Grandiflorum), Tuberose Absolute, Orange Flower Absolute (Neroli Abs.), Ylang Ylang Absolute, Mimosa Absolute, Orris Absolute, Violet Leaf Absolute, Benzoin Absolute, Labdanum Absolute, Opoponax Absolute, Peru Balsam Absolute, Tolu Balsam Absolute, Mastic Absolute, Frankincense Absolute, Sandalwood Absolute, Vetiver Absolute, Patchouli Absolute, Nagarmotha Absolute, Galbanum Absolute, Cassie Absolute, Acacia Absolute

### Accord sub-categories
Floral Accord, Oriental Accord, Woody Accord, Fresh/Aquatic Accord, Citrus Accord, Gourmand Accord, Fougère Accord, Chypre Accord, Amber Accord, Musk Accord, Leather Accord, Tobacco Accord, Earthy/Rooty Accord, Green Accord, Powdery Accord

## Migration Plan (3 migrations)

1. **Create `sub_categories` table** — schema + seed data
2. **Add `sub_category_id` FK to `materials`** — nullable initially, set default value
3. **Drop `categories` + `material_category`** — destructive, no data to preserve

Migration order ensures: new system is live before old is removed.
### Materials with `Fixative` or `Solvent` kategori (legacy)

The old `kategori` column had `Fixative` and `Solvent` enum values that mapped to the old categories system (or had no mapping at all — `Fixative` and `Solvent` were never in the category list). During the old migration (`000003`), only Top/Mid/Base got pivot rows — Fixative/Solvent materials ended up with **zero categories**.

In this new migration, those materials get `sub_category_id = NULL` (the new column is nullable). They display as **no sub-category** on index/formula views. This is correct behavior — Fixative/Solvent describe the material's functional role, not its olfactory type. The user assigns a proper Type + Sub-Category when they next edit the material.
## UI: Material Create/Edit Form

### Flow
1. User picks **Type** from dropdown (Aromachemical / Essential Oil / Absolute / Accord)
2. **Sub-Category** field appears (searchable combobox):
   - Type to filter/search sub-categories for the selected Type
   - Arrow keys + Enter to select
   - If typed name not in list → "Add [name]" option appears → creates as `is_custom = true` inline
3. Sub-Category resets when Type changes

### Implementation (Livewire)
- `$tipe` — string, drives which sub-categories load
- `$subCategoryId` — int|null, selected sub-category
- `$subCategorySearch` — string, search term for combobox
- `$filteredSubCategories` — computed from DB based on `$tipe` + `$subCategorySearch`
- On `$tipe` change: clear `$subCategoryId`, re-query sub-categories
- On save: if "add new" selected → create SubCategory first, then save Material

### Validation
```php
'tipe' => 'required|in:Aromachemical,Essential Oil,Absolute,Accord',
'subCategoryId' => 'required|exists:sub_categories,id',
```

## Testing Strategy

- Pest + RefreshDatabase
- Test: Type + SubCategory create/edit, index shows sub-category name
- Test: "Add new custom" flow (create SubCategory inline)
- Test: Fixative/Solvent legacy materials get NULL sub_category_id
- Test: formula material dropdown shows sub-category name

## Index Page Changes

Replace "Categories" column with "Sub-Category" column showing `material.subCategory->name`.
Also show Type column (already present).

## Formula Views Changes

Replace `Material::with("categories:id,name")` with `Material::with("subCategory")`.
Option text: `"{{ $opt['nama'] }} ({{ $opt['sub_category'] }})"` → shows sub-category name instead of multi-category.

## Boundaries

- **Always:** seed data before dropping old tables, test Type → SubCategory flow, create + edit + index all work
- **Ask first:** modifying seed data list, changing migration order, adding new dependencies
- **Never:** drop categories before sub_categories is seeded and materials can reference it, edit vendor files

## Success Criteria

- [ ] `sub_categories` table created with 4 types of seed data
- [ ] `categories` + `material_category` tables dropped
- [ ] Material create: Type dropdown shows Accord as 4th option
- [ ] Material create: Sub-Category searchable combobox appears after Type selected
- [ ] Material create: typing unknown name shows "Add [name]" → creates custom sub-category
- [ ] Material edit: pre-filled with saved sub-category
- [ ] Material index: shows sub-category name instead of old categories
- [ ] Formula create/edit: material dropdown shows sub-category name
- [ ] Old Fixative/Solvent materials get `sub_category_id = NULL` (displayed as no sub-category)
- [ ] `php artisan migrate:fresh --seed` — clean, no errors
- [ ] `php artisan test` — all green

## Open Questions

- None — all assumptions confirmed. Ready to implement.
