# Spec: Formula — Material Breakdown & Concentration/Bottle System

## Assumptions I'm Making

1. **`gram` field must become a required input** in the create/edit form — currently it exists in the DB pivot but the form only collects `persentase`. Without gram input, "breakdown from gram" has no data to work with.
2. **A new formula detail page (`/formulas/{id}`)** is needed to show the breakdown after save — there's currently no show route.
3. **Concentration enum** will use a native PHP 8.1 backed enum (`App\Enums\JenisKonsentrasi`) — consistent with how `kategori`/`tipe` work on Material.
4. **Volume presets are just labels** — user types any number, but we suggest 30/50/100/200 as quick-select buttons.
5. **Breakdown calculation happens client-side (Alpine.js)** — same pattern as existing `hppPerMl` computed. No server round-trip.
6. **Cross-validation warning happens server-side** — on `save()`, compare gram-derived % vs manual %, emit warning (not block save).

→ Correct me now if any of these are wrong.

## Objective

Add two capabilities to the Formula module:

1. **Material Breakdown** — After composing a formula, show each material's actual percentage (computed from `gram / totalGram × 100`) alongside the manually-input percentage. Warn on >1% discrepancy.
2. **Concentration & Bottle Volume** — User picks a concentration type (EDP/EDT/etc.) and a target bottle size. System recommends concentrate ml range. Displayed as informational guide, not enforced.

## Tech Stack

Same as existing: Laravel 13, Livewire, Alpine.js, Blade, Flux UI.

## Commands

```
Dev:    php artisan serve
Test:   php artisan test
Migrate: php artisan migrate
```

## Project Structure (changes only)

```
app/
├── Enums/
│   └── JenisKonsentrasi.php          ← NEW — backed enum
├── Models/
│   └── Formula.php                   ← MODIFIED — add fillable fields, cast enum
database/
├── migrations/
│   └── 2025_07_02_000001_add_concentration_to_formulas.php  ← NEW
resources/
├── views/pages/
│   ├── ⚡formulas-create.blade.php   ← MODIFIED — add gram input, concentration, volume
│   ├── ⚡formulas-edit.blade.php     ← MODIFIED — same additions
│   └── ⚡formulas-show.blade.php     ← NEW — breakdown detail page
routes/
│   └── web.php                       ← MODIFIED — add show route
```

## Code Style

Follow existing pattern exactly — anonymous Livewire component, `x-data` Alpine inline, `card` CSS classes:

```php
// Enum — simple, backed by string
enum JenisKonsentrasi: string
{
    case PARFUM = 'Parfum/Extrait';
    case EDP    = 'EDP';
    case EDT    = 'EDT';
    case EDC    = 'Cologne/EDC';

    public function label(): string { return $this->value; }

    public function rangeMin(): float { return match($this) {
        self::PARFUM => 20, self::EDP => 15, self::EDT => 5, self::EDC => 2,
    };}
    public function rangeMax(): float { return match($this) {
        self::PARFUM => 30, self::EDP => 20, self::EDT => 15, self::EDC => 5,
    };}
}
```

## Testing Strategy

- Pest + RefreshDatabase in `tests/Feature/`
- Key tests:
  1. `Formula` can store `jenis_konsentrasi` and `volume_botol_ml`
  2. Breakdown: gram-derived % matches expected values
  3. Cross-validation: >1% discrepancy between manual % and gram % is detected
  4. Concentration recommendation: returns correct ml range for EDP + 30ml bottle

## Boundaries

- **Always:** Run tests before commits, keep enum case-sensitive for SQLite (`'EDP'` not `'edp'`)
- **Ask first:** Any schema changes beyond what's spec'd, adding new UI components outside Flux
- **Never:** Remove existing fields, break current formula CRUD flow, add service classes

## Detailed Design

### 1. Database Migration

```php
// add_concentration_to_formulas.php
$table->string('jenis_konsentrasi')->nullable(); // EDP, EDT, Parfum/Extrait, Cologne/EDC
$table->unsignedInteger('volume_botol_ml')->nullable();
```

Nullable for backward compat with existing data.

### 2. Formula Model Changes

- Add `jenis_konsentrasi`, `volume_botol_ml` to `$fillable`
- Add cast: `'jenis_konsentrasi' => JenisKonsentrasi::class`
- Add computed property `reakomendasiKonsentratMl()` → returns `[min_ml, max_ml]` based on `jenis_konsentrasi × volume_botol_ml`

### 3. Form Changes (create + edit)

**New fields in "Formula Info" card:**
- Concentration type: `<select>` with 4 options from enum
- Bottle volume: preset buttons (30, 50, 100, 200 ml) + free input

**Gram field becomes visible:**
- Add `gram` input per material row (currently only `persentase` is shown)
- Gram and % are both editable — they're independent inputs
- Alpine computes `gramDerivedPct` per material: `(mat.gram / totalGram) × 100`

**New "Material Breakdown" card (visible after ≥1 material with gram > 0):**
- Table: Material | Manual % | Gram-derived % | Diff | Status (✓ / ⚠)
- Uses existing `card` CSS pattern
- Warning badge (terracotta color) if |diff| > 1%

**New "Concentration Guide" card (visible when jenis_konsentrasi selected + volume_botol_ml > 0):**
- Shows: "EDP (15-20%) in 30ml bottle → Concentrate: 4.5ml – 6ml, Solvent: 24ml – 25.5ml"
- Pure info, no enforcement

### 4. Formula Show Page

- Route: `GET /formulas/{id}` → `formulas.show`
- Displays: nama, deskripsi, concentration type, bottle volume
- Material breakdown table (same as form card, but computed from saved data)
- Concentration recommendation
- Link to edit

### 5. Server-Side Cross-Validation (save method)

On save, if any material has `gram > 0`:
- Compute `totalGram = sum(all grams)`
- For each material: `computedPct = gram / totalGram × 100`
- If `|computedPct - manualPct| > 1` → add warning session flash (not error — save proceeds)

## Success Criteria

- [ ] `formula` table has `jenis_konsentrasi` and `volume_botol_ml` columns
- [ ] Create/edit form shows concentration selector and bottle volume input
- [ ] Create/edit form shows gram input per material
- [ ] Material breakdown card shows manual %, gram-derived %, diff, and warning
- [ ] Concentration guide shows correct ml range
- [ ] Show page displays full breakdown after save
- [ ] Cross-validation warns (not blocks) on >1% discrepancy
- [ ] All existing tests pass + new tests pass
- [ ] Backward compatible: existing formulas with null concentration still work

## Open Questions

1. **gram is currently nullable** in DB — should I make it required for new entries, or keep nullable? (I lean toward: required for materials with >0% composition, but don't break old data)
2. **Show page design** — keep it minimal (like index table style) or more detailed card layout?
3. **Volume presets** — just 30/50/100, or also 15/20/200/500?
