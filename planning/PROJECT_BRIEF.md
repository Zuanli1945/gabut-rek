# PROJECT BRIEF — Ventura of Scent

> **Status:** BRIEF — Diisi oleh user, dibaca oleh AI. Isi lengkap — semua section terisi.

---

## 1. Vision

### Elevator Pitch

Ventura of Scent adalah platform all-in-one untuk brand parfum indie — menggabungkan e-commerce storefront, CRM pelanggan, R&D formula management, produksi & inventory, marketing automation, dan analytics dalam satu ekosistem monolithic. Satu platform untuk mengelola seluruh lifecycle brand parfum dari riset aroma hingga pengiriman ke pelanggan.

### Problem Statement

- Brand parfum indie menggunakan 3-5 tools terpisah (Shopify, Google Sheets, WA Business, CRM terpisah) — data tersebar, tidak terintegrasi
- Formula development tanpa version control — sulit tracking iterasi, rollback, atau复用 formula lama
- Costing manual di spreadsheet — rawan error, tidak real-time, margin tidak terpantau
- Stock bahan baku sering habis tanpa early warning — produksi terhambat
- Tidak ada sistem pelanggan terpadu — segmentasi berdasarkan preferensi scent tidak mungkin
- Marketing campaign manual — drip campaign, referral, abandoned cart recovery tidak terotomatisasi
- Tidak ada visibility ke COGS per SKU secara real-time

### Target Users

| Role | Deskripsi | Kebutuhan Utama |
|------|----------|----------------|
| Owner | Founder brand parfum indie — single founder atau tim kecil | Dashboard overview (revenue, margin, stock), decision-making, marketing campaign management |
| Production Staff | Staf produksi yang meracik dan mengemas | Formula access, batch production, stock deduction, production scheduling |
| Customer Service | CS yang handle order dan pelanggan | Customer history, order management, abandoned cart recovery, omnichannel messaging |
| Customer | End customer pembeli parfum | Storefront browsing, fragrance discovery, sample/decant purchase, pre-order, referral program |

---

## 2. Project Type & Tech Stack

| Item | Pilih |
|------|-------|
| Project Type | Monolith |
| Backend | Laravel |
| Framework Version | 13 |
| Frontend | Blade + Livewire + Flux + TailwindCSS |
| Database | MySQL / PostgreSQL |
| Cache | Redis |
| Queue | Redis |
| Object Storage | Local (public/storage) |
| Arsitektur | Modular Monolith — domain bisnis dipisah dalam folder terpisah di `apps/` |

---

## 2b. UI/UX Template & Design System

| Item | Value |
|------|-------|
| Template HTML Provided? | TIDAK |
| Framework UI | TailwindCSS + Flux 2 |
| Custom Design System | VOC Design System (existing: warm atelier, cream/amber palette, Cormorant Garamond + Inter) |

---

## 3. Core Features (MVP)

1. **Storefront** — Katalog produk, fragrance pyramid display, sample/decant purchase, pre-order dengan progress bar & slot counter
2. **CRM** — Customer profiling (preferensi scent family, purchase history), segmentasi, abandoned cart recovery
3. **Formula & R&D** — Version control formula, database aromachemical (nama, supplier, harga/ml, stok, IFRA limit), cost simulator, batch traceability
4. **Production & Inventory** — Auto-deduct bahan baku saat batch diproduksi, low-stock alert, production scheduling, supplier management
5. **Marketing Automation** — Email/WA drip campaign, referral/affiliate dengan komisi, automated abandoned cart recovery
6. **Analytics Dashboard** — Revenue real-time, COGS per SKU, margin per produk, sales forecasting sederhana, top-selling materials
7. **Admin RBAC** — Role-based access: owner (full), produksi (formula, inventory, production), CS (orders, customers, cart recovery)

---

## 4. Non-Goals / Out of Scope

- Mobile app native (Phase 2) — web-first
- Multi-warehouse management
- B2B wholesale portal
- AI-powered fragrance recommendation engine
- Point of Sale (POS) offline
- Multi-currency / multi-language
- Shipping/logistics integration (manual fulfillment dulu)
- Accounting/finance integration

---

## 5. User Roles & Permissions

| Role | Deskripsi | Level Akses | Module Scope |
|------|----------|------------|-------------|
| Owner | Founder — akses penuh ke semua modul | Full | Semua modul |
| Produksi | Staff produksi — racik, kemas, kelola stok & formula | Write | Formula & R&D, Production & Inventory |
| CS | Customer Service — order, pelanggan, cart recovery | Write | CRM (read customer, manage orders, abandoned cart), Storefront (manage products) |
| Customer | End user — beli, lihat profil, referral link | Read | Storefront (beli), profil sendiri, referral |

---

## 6. Core Business Logic & Rules

- JIKA customer melakukan pre-order MAKA counter slot berkurang dan progress bar terupdate real-time
- JIKA batch produksi dijalankan MAKA stok bahan baku auto-deduct berdasarkan formula composition dan batch volume
- JIKA stok bahan baku < threshold MAKA trigger low-stock alert ke dashboard dan email ke produksi
- JIKA total persentase material dalam formula ≠ 100% MAKA sistem beri warning (tidak blokir — perfumer kadang butuh eksperimen)
- JIKA customer add to cart tapi tidak checkout dalam 1 jam MAKA trigger abandoned cart recovery (email setelah 1 jam, WA setelah 4 jam)
- JIKA referral link diklik dan terjadi pembelian MAKA komisi otomatis ditambahkan ke akun referrer
- JIKA margin per SKU < target MAKA dashboard analytics highlight merah
- JIKA user role = Produksi MAKA tidak bisa akses analytics dashboard, marketing automation, atau RBAC management

---

## 7. Key Workflows

### Workflow: Pre-Order

```text
Admin set pre-order (product, kuota, deadline) → Customer lihat progress bar di storefront → Customer bayar DP/full → Slot counter decrement → Progress bar update → Deadline tercapai → Notifikasi ke produksi → Batch production → Fulfillment
```

### Workflow: Batch Production

```text
Produksi pilih formula → Set batch volume → Sistem hitung kebutuhan bahan baku → Cek stok (warning jika kurang) → Konfirmasi produksi → Stok bahan baku auto-deduct → Inventory produk jadi bertambah → Batch traceability record dibuat
```

### Workflow: Abandoned Cart Recovery

```text
Customer add to cart tanpa checkout → Timer 1 jam → Email reminder otomatis → Timer 4 jam (jika masih belum checkout) → WA reminder otomatis → Jika 24 jam masih belum checkout → Mark sebagai lost, simpan data untuk segmentasi ulang
```

### Workflow: New Formula Development

```text
Perfumer create formula → Add materials dengan persentase → Set note position (top/mid/base) → Cost simulator hitung HPP real-time → IFRA checker validasi limit → Save v1.0 → Next iteration → Save v1.1 → Version history tersimpan
```

---

## 8. Architecture Philosophy

### Modular Monolith

Domain bisnis dipisah dalam folder modular di `apps/` — pisah routing, model, service, view per domain. Satu deployable unit. Tidak ada komunikasi HTTP antar modul — langsung Service-to-Service via PHP interfaces.

### Event-Driven for Marketing

Marketing automation menggunakan Laravel event + listener yang di-queue ke Redis. Event: `OrderPlaced`, `CartAbandoned`, `CustomerRegistered`, `PreOrderFulfilled` → Listener: email/WA campaign, referral tracking, analytics update.

---

## 9. Integration Points

| Service | Type | Purpose | Auth Method | Critical? |
|---------|------|---------|-----------|----------|
| WhatsApp API (Fonnte/WATI) | REST API | Drip campaign WA, abandoned cart recovery | API Key | ✅ |
| Email (Mailgun/SES) | SMTP | Drip campaign email, order notification | SMTP credentials | ✅ |
| Midtrans/Xendit | REST API | Payment gateway | API Key + Server Key | ✅ |
| RajaOngkir | REST API | Shipping cost calculation | API Key | ❌ (phase 2) |

---

## 10. Key Architecture Decisions

- **Modular Monolith:** Setiap domain bisnis adalah module terpisah dengan folder sendiri (Modules/{Domain}/) — masing-masing punya Routes, Models, Services, Livewire Components, Views
- **Event-Driven Marketing:** Semua automation marketing di-trigger oleh event — tidak ada cron polling, tidak ada coupling langsung
- **Cost Simulator Real-Time:** HPP per formula dihitung live dari harga material per satuan × persentase — tidak ada perhitungan manual
- **Slot Counter Pre-Order:** Menggunakan Redis untuk atomic decrement counter — menghindari race condition
- **IFRA Limit Checker:** Validasi di Service layer — reusable dari form dan API

---

## 11. Success Criteria

| Metric | Target | Cara Ukur |
|--------|--------|----------|
| Formula versioning | 100% formula punya version history | Query count formula_versions / formula > 1 |
| Auto-deduct accuracy | 100% akurat | Samakan stock sebelum/after batch produksi |
| Abandoned cart recovery rate | ≥15% conversion | (recovered carts / abandoned carts) × 100 |
| Email/WA delivery | ≥95% delivered | Provider analytics |
| Dashboard refresh | ≤5 detik | Time query analytics endpoint |
| User adoption | All roles aktif menggunakan platform minggu pertama | Login frequency per role |

---

## 12. Final Goal / North Star

Membangun platform all-in-one yang memungkinkan brand parfum indie mengelola seluruh operasional — dari riset formula hingga pengiriman ke pelanggan — dalam satu ekosistem terintegrasi tanpa perlu 5 tool berbeda.
