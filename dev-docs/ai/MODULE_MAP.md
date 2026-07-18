# MODULE_MAP

> **Status:** DATA FILE — Mapping visual antara modul bisnis dan komponen kode.

---

## Module to Code Map

### Monolith

| Module | Route File | Controllers | Models | Views |
|--------|-----------|-------------|--------|-------|
| Auth | `routes/web.php`, `routes/settings.php` | `app/Http/Controllers/Controller.php` | `app/Models/User.php` | `resources/views/pages/auth/*` |
| Dashboard | `routes/web.php:8` | — | — | `resources/views/pages/dashboard.blade.php` |
| Materials | `routes/web.php:10-18` | — | `app/Models/Material.php`, `app/Models/SubCategory.php`, `app/Models/Category.php` | `resources/views/pages/materials-*.blade.php` |
| Formulas | `routes/web.php:20-31` | — | `app/Models/Formula.php`, `app/Models/FormulaMaterial.php` | `resources/views/pages/formulas-*.blade.php` |
| Products | `routes/web.php:33-41` | — | `app/Models/Produk.php`, `app/Models/BiayaProduksi.php` | `resources/views/pages/products-*.blade.php` |
| Settings | `routes/settings.php` | — | `app/Models/User.php` | `resources/views/pages/settings/*` |
| Passkeys | `routes/settings.php:21-25` | — | — | `resources/views/components/passkey-*.blade.php` |

---

## Shared Infrastructure Map

| Area | Path | Notes |
|------|------|-------|
| Auth | `app/Actions/Fortify/` | Fortify actions |
| Enums | `app/Enums/` | JenisKonsentrasi, MaterialType |
| Layouts | `resources/views/layouts/` | app.blade.php, auth.blade.php |
| Components | `resources/views/components/` | Shared Blade components |
| Flux Icons | `resources/views/flux/icon/` | Custom Flux icons |
| Console | `app/Console/` | Artisan commands |
| Tests | `tests/` | Pest tests |
