# Spec: Multi-Category Material

## Objective

Replace single `kategori` column on `materials` with many-to-many categories system. One material can have multiple categories (e.g. "Top + Modifier"). Migration must preserve existing data.

## Tech Stack

Laravel 13, Livewire, Flux UI, Blade, SQLite/MySQL, Pest

## Mapping: Old → New categories

| Old value | New category |
|---|---|
| `Top` | Top Note |
| `Mid` | Middle/Heart Note |
| `Base` | Base Note |
| `Fixative` | _(new — no legacy mapping)_ |
| `Solvent` | _(new — no legacy mapping)_ |
| — | Modifier (new) |
| — | Blender (new) |

New system has 5 categories: **Top Note, Middle/Heart Note, Base Note, Modifier, Blender**.

### Materials with `Fixative` or `Solvent` kategori

No equivalent category exists. During migration, these materials get **no pivot row** (zero categories). The `kategori` column is dropped after migration regardless. Behavior: material's `categories` relation returns empty collection. UI (index, formula dropdown) displays blank for the Categories column. No data loss — the value is gone but was never semantically meaningful in the new system.

### Materials with null/empty kategori

Existing `kategori` column is `enum(...)` NOT NULL, so no nulls exist. However, if `migrate:fresh` runs on a DB with materials created after the enum was dropped (via factory/other path), those get no pivot row too. Spec does not guarantee categories for materials that didn't have a `kategori` value.

## Commands

```
Build: npm run build
Test: php artisan test
Migrate: php artisan migrate
Fresh: php artisan migrate:fresh --seed
```

## Project Structure

```
database/migrations/
  ####_##_##_000001_create_categories_table.php   → NEW
  ####_##_##_000002_create_material_category_table.php → NEW
  ####_##_##_000003_migrate_kategori_to_categories.php → NEW (data migration + drop column)
database/seeders/
  CategorySeeder.php → NEW (seeds 5 categories)
  DatabaseSeeder.php → MODIFIED (calls CategorySeeder)
app/Models/
  Category.php → NEW
  Material.php → MODIFIED (remove kategori, add categories() relation)
app/Livewire/ (or Blade components)
  ⚡materials-create.blade.php → MODIFIED
  ⚡materials-edit.blade.php → MODIFIED
  ⚡materials-index.blade.php → MODIFIED
  ⚡formulas-create.blade.php → MODIFIED (replace categories with new category system in material option text)
  ⚡formulas-edit.blade.php → MODIFIED (same — NOTE: Alpine x-for bug in select pre-selection is a known separate issue, NOT addressed in this spec)
tests/Feature/
  MultiCategoryMaterialTest.php → NEW
  FormulaEnhancementTest.php → MODIFIED (remove kategori from tests)
  HargaJualTest.php → MODIFIED (remove kategori from tests)
```

## Code Style

Existing project patterns (Eloquent-centric, Livewire single-file components). No service classes.

## Testing Strategy

- Pest + RefreshDatabase
- Test: create material with 2 categories → assert pivot saved
- Test: create material with Fixative/Solvent kategori → assert zero pivot rows (no category mapped)
- Test: edit material → pre-filled with saved categories
- Test: old kategori data migrated to pivot correctly
- Test: index page shows comma-separated categories

## Boundaries

- Always: migration preserves existing data, seed 5 categories, test multi-select
- Ask first: changing migration order or adding new dependencies
- Never: dropping column before migrating data

## Success Criteria

- [ ] Migration creates `categories` + `material_category` tables
- [ ] 5 categories seeded (Top Note, Middle/Heart Note, Base Note, Modifier, Blender)
- [ ] Existing `kategori` values migrated to rows in `material_category` pivot
- [ ] Old `kategori` column dropped after migration
- [ ] Material create form: multi-select (checkboxes) instead of single select
- [ ] Material edit form: pre-filled categories
- [ ] Material index: shows category names (comma-separated)
- [ ] Formula material dropdown: shows category names (not single kategori)
- [ ] All existing tests updated to work without `kategori` column
- [ ] New tests cover: create with multi, edit pre-fill, migration integrity, Fixative/Solvent → no pivot row
- [ ] `php artisan test` — all green (except pre-existing RegistrationTest)
