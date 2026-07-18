# Aristektur — Sillage

> Dokumen arsitektur teknis untuk aplikasi manajemen parfum.

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 13 |
| UI | Livewire + Flux UI + Blade |
| Auth | Laravel Fortify (email, 2FA, passkeys) |
| CSS | Vite + Tailwind |
| Testing | Pest + Larastan |
| Database | MySQL/SQLite (Eloquent ORM) |

## Domain Model

```
┌──────────┐     pivot     ┌──────────────┐     belongsTo     ┌───────────────┐
│ Material │◄─────────────►│ FormulaMaterial│◄────────────────│    Formula    │
│          │  1:N          │  (formula_id, │                  │               │
│  nama    │               │  material_id, │                  │  nama_formula │
│  kategori│               │  persentase,  │                  │  deskripsi    │
│  tipe    │               │  gram,        │                  └───────┬───────┘
│  scent   │               │  note_posisi) │                          │
│  harga   │               └───────────────┘                          │ hasMany
│  stock   │                                                         ▼
└────┬─────┘                                                 ┌───────────────┐
     │                                                       │ BiayaProduksi │
     │ belongsTo (as solvent)                                │               │
     └──────────────────────────────────────────────────────►│ formula_id    │
                                                             │ solvent_id    │
                                                             │ cogs_per_unit │
                                                             │ harga_jual    │
                                                             │ margin        │
                                                             └───────┬───────┘
                                                                     │ hasMany
                                                                     ▼
                                                             ┌───────────────┐
                                                             │     Produk    │
                                                             │               │
                                                             │ nama_produk   │
                                                             │ harga_jual    │
                                                             │ stock         │
                                                             └───────────────┘
                                                                     │
                                                                     │ belongsToMany
                                                                     ▼
                                                             ┌───────────────┐
                                                             │  Formula      │
                                                             │  (pivot:      │
                                                             │  formula_     │
                                                             │  produk)      │
                                                             └───────────────┘
```

### Relasi Inti

| Model | Relasi | Keterangan |
|---|---|---|
| `Material` | `belongsToMany(Formula)` | Via `FormulaMaterial` pivot |
| `Formula` | `hasMany(BiayaProduksi)` | Satu formula → banyak variant biaya |
| `Formula` | `belongsToMany(Material)` | Bahan penyusun formula |
| `BiayaProduksi` | `belongsTo(Formula)` | — |
| `BiayaProduksi` | `belongsTo(Material)` | Sebagai solvent |
| `BiayaProduksi` | `hasMany(Produk)` | Produk turunan dari perhitungan biaya |
| `Produk` | `belongsTo(BiayaProduksi)` | — |
| `Produk` | `belongsToMany(Formula)` | Via `formula_produk` pivot |

## Database Schema

### `materials`
```
id, nama, kategori, tipe, scent_family, harga_beli, jumlah_beli, satuan,
stock_saat_ini, timestamps
```

### `formulas`
```
id, nama_formula, deskripsi, timestamps
```

### `formula_material` (pivot)
```
id, formula_id → formulas, material_id → materials,
persentase, gram, note_posisi, timestamps
```

### `biaya_produksis`
```
id, formula_id → formulas, solvent_material_id → materials,
persentase_konsentrasi, jumlah_batch_ml, biaya_kemasan,
target_margin_persen, jumlah_unit_hasil, cogs_per_unit,
harga_jual, margin_rupiah, timestamps
```

### `produks`
```
id, biaya_produksi_id → biaya_produksis,
nama_produk, harga_jual, stock, timestamps
```

### `formula_produk` (pivot)
```
id, formula_id, produk_id, jumlah, persentase, timestamps
```

## Routing

Semua route utama butuh auth + email verification.

| Method | URI | Handler |
|---|---|---|
| GET | `/` | Welcome page |
| GET | `/dashboard` | Dashboard |
| **Materials** | | |
| GET | `/materials` | Index |
| GET | `/materials/create` | Create |
| GET | `/materials/{material}/edit` | Edit |
| **Formulas** | | |
| GET | `/formulas` | Index |
| GET | `/formulas/create` | Create |
| GET | `/formulas/{formula}/edit` | Edit |
| **Products** | | |
| GET | `/products` | Index |
| GET | `/products/create` | Create |
| GET | `/products/{product}/edit` | Edit |
| **Settings** | | |
| GET | `/settings/profile` | Profile |
| GET | `/settings/appearance` | Appearance |
| GET | `/settings/security` | Security |

> Route handler pakai Livewire component — bukan controller tradisional.

## Directory Structure

```
app/
├── Actions/Fortify/        # CreateNewUser, ResetUserPassword
├── Concerns/               # PasswordValidationRules, ProfileValidationRules
├── Console/Commands/
├── Http/Controllers/       # Base controller saja
├── Livewire/Actions/       # Logout action
├── Models/                 # Eloquent models (domain layer)
├── Providers/              # AppServiceProvider, FortifyServiceProvider

database/
├── factories/
├── migrations/             # Schema definitions
├── seeders/

resources/
├── views/
│   ├── components/         # Reusable Blade components
│   ├── flux/               # Flux UI components
│   ├── layouts/            # app, auth, sidebar, header
│   ├── pages/              # dashboard, materials, formulas, products
│   ├── partials/           # Partial views
│   └── settings/           # Profile, appearance, security, 2FA

routes/
├── web.php                 # Main routes (materials, formulas, products)
└── settings.php            # Settings routes
```

## Auth Architecture

```
User ──► Fortify
         ├── Registration (CreateNewUser action)
         ├── Login
         ├── Password Reset
         ├── Email Verification
         ├── Two-Factor Auth (2FA columns on users)
         └── Passkeys (WebAuthn via passkeys table)
```

## Perhitungan Biaya (Business Logic)

Alur kalkulasi di `BiayaProduksi`:

1. **Input**: formula + solvent material + parameter batch
2. **COGS per unit** = total biaya bahan / jumlah unit hasil
3. **Harga jual** = COGS × (1 + target_margin_persen / 100)
4. **Margin rupiah** = harga_jual - cogs_per_unit

## Key Decisions

| Keputusan | Alasan |
|---|---|
| Livewire-first, no controllers | Semua UI via Livewire page components — minim boilerplate |
| Fortify auth | Full-featured auth (2FA + passkeys) tanpa write dari nol |
| Eloquent-centric | Domain logic di model & relasi, bukan service class |
| Flux UI | Komponen UI premium untuk Blade/Livewire |
| Pest untuk testing | Modern, expressif, lebih ringkas dari PHPUnit |

## Dependencies

**Production:**
- `laravel/framework` — Core framework
- `laravel/fortify` — Auth scaffolding
- `livewire/livewire` — Reactive UI
- `livewire/flux` — UI component library
- `livewire/blaze` — Livewire utilities

**Development:**
- `pestphp/pest` — Testing
- `laravel/pint` — Code style
- `larastan` — PHPStan for Laravel
- `laravel/sail` — Docker dev env

---

*Dokumen ini dihasilkan dari analisis kode sumber per 2 Juli 2026.*
