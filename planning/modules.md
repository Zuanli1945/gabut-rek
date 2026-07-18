# Module Breakdown — Ventura of Scent

> **Status:** PLANNING — Breakdown modul, dependency, prioritas build.

---

## Project Type Declaration

| Item | Value |
|------|-------|
| Project Type | Monolith |
| Code Location | `apps/` |

---

## 1. Module Map

| # | Module | Deskripsi | Priority | Dependencies |
|---|--------|----------|----------|-------------|
| 1 | Formula & R&D | Extend existing formula management, version control, cost simulator | P0 | — |
| 2 | Production & Inventory | Batch production, auto-deduct, low-stock alert, supplier management | P0 | Formula & R&D |
| 3 | Storefront | Product catalog, fragrance pyramid, sample/decant, pre-order | P0 | Formula & R&D, Production |
| 4 | CRM | Customer profiling, segmentasi, purchase history | P0 | Storefront (order data) |
| 5 | Marketing Automation | Drip campaign, referral/affiliate, abandoned cart recovery | P1 | CRM, Storefront |
| 6 | Analytics Dashboard | Revenue, COGS, margin, forecasting | P1 | Semua modul |
| 7 | Admin RBAC | Role & permission management, user management | P0 | — |

---

## 2. Module Detail

### Formula & R&D

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/formulas`, `/admin/materials` |
| Middleware | `auth`, `role:owner|produksi` |
| User Roles | Owner (full), Produksi (write) |

**Key Features:** Formula versioning, aromachemical DB with IFRA limits, cost simulator, batch traceability

### Production & Inventory

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/production`, `/admin/inventory` |
| Middleware | `auth`, `role:owner|produksi` |
| User Roles | Owner (full), Produksi (write) |

**Key Features:** Batch production with auto-deduct, low-stock alert, production schedule, supplier management

### Storefront

| Item | Detail |
|------|--------|
| Route Prefix | `/` (public), `/products`, `/cart`, `/checkout` |
| Middleware | `web` (public), `auth:customer` (cart/checkout) |
| User Roles | Customer (buy), Owner/Produksi (manage products) |

**Key Features:** Product catalog, fragrance pyramid, sample/decant, pre-order with slot counter

### CRM

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/crm` |
| Middleware | `auth`, `role:owner|cs` |
| User Roles | Owner (full), CS (read/write) |

**Key Features:** Customer profile, segmentasi, abandoned cart list, purchase history

### Marketing Automation

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/marketing` |
| Middleware | `auth`, `role:owner` |
| User Roles | Owner only |

**Key Features:** Drip campaign builder, referral/affiliate management, campaign analytics

### Analytics Dashboard

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/analytics` |
| Middleware | `auth`, `role:owner` |
| User Roles | Owner only |

**Key Features:** Revenue overview, COGS per SKU, margin tracking, sales forecasting, top materials

### Admin RBAC

| Item | Detail |
|------|--------|
| Route Prefix | `/admin/settings` |
| Middleware | `auth`, `role:owner` |
| User Roles | Owner only |

**Key Features:** Role CRUD, user management with role assignment, permission gates

---

## 3. Dependency Graph

```text
Formula & R&D (Foundation)
  └── Production & Inventory (depends on formula data)
       └── Storefront (depends on production output)
            └── CRM (depends on order data from storefront)
                 └── Marketing Automation (depends on CRM data)

Admin RBAC (Foundation — parallel with Formula)
Analytics Dashboard (depends on all modules — built last)
```

---

## 4. Build Order

| Phase | Modul | Urutan | Alasan |
|-------|-------|--------|--------|
| 1 | Admin RBAC + Auth | 1 | Semua modul butuh role |
| 2 | Formula & R&D | 2 | Foundation — extend existing code |
| 3 | Production & Inventory | 3 | Depends on formula |
| 4 | Storefront | 4 | Depends on products from production |
| 5 | CRM | 5 | Depends on order data |
| 6 | Marketing Automation | 6 | Depends on CRM |
| 7 | Analytics Dashboard | 7 | Depends on all data |
