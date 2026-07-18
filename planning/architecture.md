# Architecture Plan — Ventura of Scent

> **Status:** PLANNING — Cetak biru arsitektur sistem.

---

## 1. System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     Laravel Monolith                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ Storefront│ │    CRM   │ │Formula&R&D│ │Production│   │
│  │  Module   │ │  Module  │ │  Module  │ │  Module  │   │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘   │
│       │            │            │            │          │
│  ┌────┴────────────┴────────────┴────────────┴─────┐    │
│  │              Shared Services Layer               │    │
│  │  (Auth, RBAC, Notification, Analytics, Audit)    │    │
│  └───────────────────────┬──────────────────────────┘    │
│                          │                                │
│  ┌───────────────────────┴──────────────────────────┐    │
│  │              Infrastructure Layer                 │    │
│  │   MySQL/PostgreSQL | Redis | Filesystem | Queue   │    │
│  └──────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

### Module Communication

- Modules communicate through **Service Interfaces** — no direct HTTP calls between modules
- Events dispatched for cross-module concerns (OrderPlaced → Inventory reserved, Marketing triggered)
- Each module has its own Routes, Livewire Components, Models, Services, and Views

### Folder Structure (apps/)

```
apps/
├── app/
│   ├── Modules/
│   │   ├── Storefront/
│   │   │   ├── Routes/
│   │   │   ├── Http/Livewire/
│   │   │   ├── Models/
│   │   │   ├── Services/
│   │   │   └── views/
│   │   ├── CRM/
│   │   ├── Formula/
│   │   ├── Production/
│   │   ├── Marketing/
│   │   ├── Analytics/
│   │   └── Admin/
│   ├── Shared/
│   │   ├── Services/ (Notification, RBAC, Audit)
│   │   ├── Traits/
│   │   └── Interfaces/
│   ├── Enums/
│   ├── Console/
│   └── Providers/
├── resources/views/
│   ├── storefront/
│   ├── admin/
│   └── layouts/
└── routes/
    ├── web.php
    └── admin.php
```

## 2. Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| Backend Framework | Laravel 13 | Modular monolith |
| Frontend | Livewire 4 + Flux 2 + TailwindCSS 4 | Full-page components per modul |
| Database | MySQL/PostgreSQL | Single database, multiple schemas per module |
| Cache | Redis | Session, cache, pre-order counter |
| Queue | Redis | Marketing automation, email, analytics refresh |
| Task Scheduling | Laravel Scheduler | Cron untuk cart recovery, analytics aggregation |
| Search | MySQL FULLTEXT / Meilisearch (phase 2) | Product catalog search |

## 3. Authentication & RBAC

- Laravel Fortify + Passkeys (existing)
- Role-based via Spatie Permission atau custom Gate
- Middleware: `role:owner` pada routes admin sensitive
- Customer auth via separate guard (`web` for admin, `customer` for storefront)

## 4. Event-Driven Flow

```text
CustomerOrders → OrderPlaced event
  ├── InventoryReserved listener
  ├── MarketingDripTriggered listener
  └── AnalyticsUpdated listener

CartAbandoned event
  ├── EmailReminderScheduled listener (1h delay)
  └── WaReminderScheduled listener (4h delay)

BatchProduced event
  ├── InventoryDeducted listener
  ├── LowStockChecked listener
  └── AnalyticsUpdated listener
```
