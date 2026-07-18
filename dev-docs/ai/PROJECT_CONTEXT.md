# PROJECT_CONTEXT

> **Status:** DATA FILE — AI mengisi data project berdasarkan analisis codebase.
> **Purpose:** Overview sistem, stack teknologi, dan struktur project.

---

## System Overview

VOC Atelier adalah aplikasi perfumer workspace untuk meracik parfum. Mengelola material (bahan baku), formula (resep), produk jadi, dan biaya produksi. Target pengguna: perfumer rumahan dan UKM parfum.

Fitur inti: manajemen material (aromachemical, essential oil, absolute, accord), formulasi parfum dengan kalkulasi konsentrat berdasarkan jenis konsentrasi (Parfum/EDP/EDT/EDC), manajemen produk jadi, dan tracking biaya produksi.

---

## Project Type Declaration

| Item | Value |
|------|-------|
| Project Type | Monolith |
| Git Location | Root (standard Laravel structure) |

---

## Runtime Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 + Livewire 4 |
| Frontend | Flux 2 + TailwindCSS 4 + Vite 8 |
| Database Primary | SQLite (default, switchable to MySQL/PostgreSQL) |
| Cache | File/database (default), Redis available |
| Queue | Database (default) |
| Web Server | Nginx / Artisan serve |

---

## UI/UX Template & Framework

### Template Status

| Item | Value |
|------|-------|
| Template HTML Provided? | TIDAK |
| UI Framework | TailwindCSS 4 + Flux 2 |
| Framework Version | TailwindCSS ^4.0.7, Flux ^2.13.1 |

### Design System

Design system didokumentasikan di `DESIGN.md` — tema atelier dengan palet warm (Cream Paper #F5F1EA, Deep Ink #2B2622, Warm Amber #B8823D). Typography: Cormorant Garamond untuk display, Inter untuk body. Layout asymmetric dengan whitespace tinggi.

---

## Database Topology

| Connection | Domain / Schema | Notes |
|-----------|----------------|-------|
| `sqlite` | Main app | `database/database.sqlite` — single connection |

---

## Source Structure (Important)

| Path | Function |
|------|----------|
| `app/Models/` | Eloquent models: Material, Formula, Produk, Category, SubCategory, etc. |
| `app/Enums/` | Enums: JenisKonsentrasi (Parfum/EDP/EDT/EDC), MaterialType (Aromachemical/Essential Oil/Absolute/Accord) |
| `app/Livewire/` | Livewire components (volatile pages) |
| `app/Actions/Fortify/` | Fortify auth action classes |
| `app/Http/Controllers/` | Base Controller only (app is Livewire-driven) |
| `app/Providers/` | Service providers |
| `app/Console/` | Artisan commands |
| `app/Concerns/` | Shared traits |
| `resources/views/` | Blade views: layouts (app, auth), pages (dashboard, materials, formulas, products, settings, auth), components, partials |
| `routes/` | web.php (main routes), settings.php (settings routes), console.php |
| `database/migrations/` | 18 migration files — users, materials, formulas, formula_material, biaya_produksis, produks, categories, sub_categories, passkeys |
| `config/` | Laravel config files |
| `public/` | Public assets (build output) |
| `tests/` | Pest test files |
| `scripts/` | Node.js utility scripts (generate-theme.js) |

---

## Current Architecture Direction

Monolith Laravel + Livewire. No separate API layer — all UI is server-rendered Livewire components with Flux UI. No traditional REST controllers. Auth via Laravel Fortify + Passkeys.

---

## Uncertainty Markers

- Assumption based on repository analysis: Database is SQLite by default (`.env` shows no DB config override)
- Assumption based on repository analysis: No queue worker config for production yet
- Assumption based on repository analysis: Design system is fully custom (no premium HTML template)
