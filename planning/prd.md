# PRD — Ventura of Scent

> **Status:** PLANNING — Dibuat dari PROJECT_BRIEF. Panduan development.

---

## 1. Product Overview

Platform all-in-one untuk brand parfum indie "Ventura of Scent". Ekosistem lengkap: storefront e-commerce, CRM, formula & R&D management, produksi & inventory, marketing automation, dan analytics — dalam satu Laravel monolith.

## 2. Feature Specification

### 2.1 Storefront

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Product Catalog | Katalog dengan filter scent family, type, price range, concentration | Browse, filter, search produk |
| Fragrance Pyramid | Display top/mid/base notes per produk dengan visual pyramid | Setiap produk punya note breakdown |
| Sample/Decant | Pembelian sample/decant dalam ukuran kecil (1-5ml) | Add sample ke cart, price prorata |
| Pre-Order | Pre-order dengan kuota slot + progress bar + deadline counter | Booking, progress visual, auto-close saat quota penuh |

### 2.2 CRM

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Customer Profile | Profil pelanggan: purchase history, preferred scent family, total spend | Lihat profil dari admin, segmentasi |
| Segmentasi | Filter customer by: total spend, scent preference, order frequency, last purchase | Segment builder UI |
| Abandoned Cart | Auto-detect cart abandoned + recovery workflow | Cron + event trigger email/WA |

### 2.3 Formula & R&D

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Formula CRUD | Create formula dengan materials, % composition, note position | Existing — extend |
| Version Control | Auto-version setiap update formula | Versi increment, history bisa di-view |
| Aromachemical DB | Database material: nama, supplier, harga/ml, stock, IFRA limit | CRUD material + fields baru |
| Cost Simulator | Real-time HPP per ml berdasarkan material composition | Live update via Alpine.js |
| Batch Traceability | Setiap batch produksi tercatat: formula version, tanggal, qty, operator | Riwayat batch per formula |

### 2.4 Production & Inventory

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Batch Production | Pilih formula → set volume → auto-calculate material needs → deduct stock | One-click production |
| Low Stock Alert | Notifikasi dashboard + email ketika stock < threshold | Configurable threshold per material |
| Production Schedule | Kalender/schedule untuk jadwal produksi | Create schedule, assign operator |
| Supplier Management | CRUD supplier, track purchase price, lead time | Supplier list per material |

### 2.5 Marketing Automation

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Drip Campaign | Schedule email/WA sequence untuk customer segment | Campaign builder, trigger on event |
| Referral/Affiliate | Referral link, track conversions, commission auto-calculate | Share link, track clicks, auto-credit |
| Abandoned Cart Recovery | Auto email (1h) + WA (4h) reminder | Trigger + log sent/recovered |

### 2.6 Analytics Dashboard

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Revenue Overview | Total revenue, MTD, growth %, per SKU breakdown | Real-time aggregation |
| COGS per SKU | Cost of goods sold per product (materials + packaging) | Calculated from formula + batch cost |
| Margin Tracking | Margin % per SKU, highlight below target | Color-coded display |
| Sales Forecasting | Simple linear projection based on 3-month trend | Chart with forecast line |
| Top Materials | Most-used materials across formulas | Table + usage count |

### 2.7 Admin RBAC

| Fitur | Deskripsi | Acceptance Criteria |
|-------|-----------|-------------------|
| Role Management | CRUD roles, assign permissions | Owner-only |
| User Management | CRUD users, assign role | Owner-only |
| Permission Gates | @can(), middleware per module | Session-based |

## 3. User Stories

- Sebagai owner, saya ingin melihat dashboard real-time revenue, margin, dan stock alerts tanpa harus buka spreadsheet
- Sebagai produksi, saya ingin memproduksi batch dengan 1 klik dan stok otomatis ter-deduct
- Sebagai CS, saya ingin melihat riwayat pembelian customer dan preferensi scent mereka
- Sebagai customer, saya ingin melihat fragrance pyramid produk sebelum membeli
- Sebagai customer, saya ingin pre-order dengan progress bar sehingga tahu kapan produk ready
