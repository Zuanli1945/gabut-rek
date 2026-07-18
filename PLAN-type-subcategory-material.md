# Implementation Plan: Type-based Sub-Category Material

## Overview

Replace multi-category checkboxes (Top/Mid/Base/Modifier/Blender) with Type→SubCategory belongsTo. New `sub_categories` table, Accord type, searchable combobox with inline "add custom", drop old `categories` + `material_category`.

## Architecture Decisions

- **Keep `tipe` column** on materials (redundant with sub_category->type but simpler migration)
- **3 migrations** in order: (1) create sub_categories + seed, (2) add FK to materials, (3) drop old tables
- **Livewire inline** for combobox — no Flux UI select component, use Alpine + Livewire
- **Pest** tests per vertical slice (not one big test file at end)

## Task List

### Phase 1: Foundation — Schema, Model, Enum, Seeder

- [ ] Task 1: SubCategory model + migration + enum + seeder with all seed data

**Acceptance:**
- `sub_categories` table created with 4 types of seed data
- `MaterialType` enum (Aromachemical, Essential Oil, Absolute, Accord)
- `SubCategory` model with `materials()` hasMany
- Seeder populates all 4 categories
- `php artisan migrate:fresh --seed` works

**Files:**
- `app/Enums/MaterialType.php` — NEW
- `app/Models/SubCategory.php` — NEW
- `database/migrations/..._create_sub_categories_table.php` — NEW
- `database/seeders/SubCategorySeeder.php` — NEW (replaces CategorySeeder)
- `database/seeders/DatabaseSeeder.php` — MODIFIED

---

- [ ] Task 2: Add sub_category_id FK to materials + drop old tables

**Acceptance:**
- `materials` has `sub_category_id` FK (nullable) → `sub_categories`
- `categories` + `material_category` tables dropped
- Old materials get `sub_category_id = NULL`
- `Material::subCategory()` belongsTo relation
- Old `Category` model deleted
- `Material` has no more `categories()` relation

**Files:**
- `database/migrations/..._add_sub_category_id_to_materials.php` — NEW
- `database/migrations/..._drop_categories_tables.php` — NEW
- `app/Models/Material.php` — MODIFIED
- `app/Models/Category.php` — DELETED (manually delete after migration runs)

---

### Checkpoint: Foundation
- [ ] `php artisan migrate:fresh --seed` clean
- [ ] `php artisan tinker` → Material with SubCategory loads correctly

### Phase 2: UI Forms — Create + Edit

- [ ] Task 3: Material Create form — Type dropdown + searchable SubCategory combobox + inline add

**Acceptance:**
- Type dropdown shows Accord as 4th option
- SubCategory dropdown appears, filtered by selected Type
- Searchable: typing filters results
- "Add [name]" appears when typed term not in list → creates custom SubCategory on save
- Validation: Type + SubCategory required

**Files:**
- `resources/views/pages/⚡materials-create.blade.php` — MODIFIED

---

- [ ] Task 4: Material Edit form — pre-filled SubCategory

**Acceptance:**
- Edit loads saved Type + SubCategory
- Changing Type resets SubCategory
- Save works correctly

**Files:**
- `resources/views/pages/⚡materials-edit.blade.php` — MODIFIED

---

### Phase 3: Display — Index + Formula views

- [ ] Task 5: Index + Formula views show SubCategory name

**Acceptance:**
- Index page shows SubCategory column (instead of old Categories)
- Formula create/edit dropdowns show `nama (sub_category_name)`
- All views load without error

**Files:**
- `resources/views/pages/⚡materials-index.blade.php` — MODIFIED
- `resources/views/pages/⚡formulas-create.blade.php` — MODIFIED
- `resources/views/pages/⚡formulas-edit.blade.php` — MODIFIED

---

### Phase 4: Tests

- [ ] Task 6: Comprehensive test for Type→SubCategory flow

**Acceptance:**
- Create material with Type + SubCategory → saved correctly
- Edit material → pre-filled, update works
- "Add custom" SubCategory created inline
- Index shows sub-category name
- Formula dropdown shows sub-category name
- All tests green

**Files:**
- `tests/Feature/SubCategoryMaterialTest.php` — NEW

---

### Final Checkpoint
- [ ] `php artisan migrate:fresh --seed` clean
- [ ] `php artisan test` all green
- [ ] `php artisan db:seed` works standalone

## Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Old `categories` seed call in DatabaseSeeder | Med | Replace with SubCategorySeeder in Task 1 |
| Formula Alpine x-for bug resurfaces | Low | Already documented as separate; not touched here |
| Seeder data too long to type | Low | Already compiled in spec |

## Open Questions

- None. Spec approved.
