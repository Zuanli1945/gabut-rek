# Database Architecture

> **Status:** DATA FILE — Arsitektur database berdasarkan migration files.

---

## Entity Relationship

```
Category (1) ──< MaterialCategory >── (M) Material
SubCategory (1) ──< (M) Material

Formula (1) ──< FormulaMaterial >── (M) Material
Formula (1) ──< (M) BiayaProduksi
Formula (M) >── FormulaProduk >── (M) Produk

Produk (1) ──< (1) BiayaProduksi
Material (1) ──< (M) BiayaProduksi (as solvent_material_id)

User (1) ──< (M) Passkey
```

## Tables

### materials
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| sub_category_id | bigint fk | nullable |
| nama | string | |
| tipe | string | Aromachemical/Essential Oil/Absolute/Accord |
| scent_family | string | nullable |
| harga_beli | decimal(10,2) | |
| jumlah_beli | decimal(10,2) | |
| satuan | string | |
| stock_saat_ini | decimal(10,2) | |

### formulas
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| nama_formula | string | |
| deskripsi | text | nullable |
| jenis_konsentrasi | string enum | Parfum/EDP/EDT/EDC |
| volume_botol_ml | decimal | |

### formula_material (pivot)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| formula_id | bigint fk | |
| material_id | bigint fk | |
| persentase | decimal | |
| gram | decimal | |
| note_posisi | string | top/middle/base |

### biaya_produksis
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| formula_id | bigint fk | |
| solvent_material_id | bigint fk | |
| solvent_persen | decimal | |
| total_biaya | decimal | |

### produks
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| nama_produk | string | |
| biaya_produksi_id | bigint fk | |
| harga_jual | decimal(10,2) | |
| stock | decimal(10,2) | |

### formula_produk (pivot)
| Column | Type | Notes |
|--------|------|-------|
| formula_id | bigint fk | |
| produk_id | bigint fk | |
| jumlah_ml | decimal | |
| persentase_komposisi | decimal | |

### categories
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| name | string | |

### sub_categories
| Column | Type | Notes |
|--------|------|-------|
| id | bigint pk | |
| category_id | bigint fk | |
| name | string | |

### material_category (pivot)
| Column | Type | Notes |
|--------|------|-------|
| material_id | bigint fk | |
| category_id | bigint fk | |

### users
Standard Laravel users table + two-factor columns + passkeys relation.
