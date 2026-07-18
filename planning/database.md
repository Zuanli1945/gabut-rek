# Database Plan — Ventura of Scent

> **Status:** PLANNING — Cetak biru database.

---

## 1. Database Topology

| Connection | DB Engine | Database Name | Purpose |
|-----------|----------|--------------|---------|
| `default` | MySQL/PostgreSQL | `ventura_of_scent` | Primary data — semua modul |

---

## 2. Entity Relationship Diagram

```text
── CORE ──
users ──< role_user >── roles
users ──< orders
users ──< customer_profiles
users ──< referral_links
users ──< abandoned_carts

── FORMULA & R&D ──
materials ──< formula_material >── formulas
formulas ──< formula_versions
formulas ──< biaya_produksis
materials ──< material_suppliers >── suppliers
materials ──< material_ifra_limits

── PRODUCTION & INVENTORY ──
formulas ──< batch_productions
batch_productions ──< batch_material_usages
materials ──< stock_movements
batch_productions ──< produks (finished goods inventory)

── STOREFRONT ──
produks ── product_variants
product_variants ──< cart_items
product_variants ──< order_items
orders ──< pre_orders
product_variants ──< pre_order_slots

── CRM ──
users ── customer_preferred_scent_families
users ──< customer_segments
orders ──< abandoned_carts

── MARKETING ──
campaigns ──< campaign_sequences
campaigns ──< campaign_recipients
referral_links ──< referral_conversions
```

---

## 3. Table Schemas

### CORE — `users` (existing, extend)
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| role_id | bigint | FK → roles, nullable | Admin role |
| phone | string | nullable | For WA marketing |
| referral_code | string | unique, nullable | Auto-generated |

### CORE — `roles`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| name | string | unique (owner, produksi, cs) |
| guard_name | string | 'web' |

### FORMULA — `materials` (existing, extend)
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| supplier_id | bigint | FK → suppliers, nullable | |
| ifra_limit | decimal(8,4) | nullable | Max % in formula |
| threshold_stock | decimal(10,2) | default 10 | Low-stock alert threshold |
| is_active | boolean | default true | Soft disable |

### FORMULA — `suppliers`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| name | string | |
| contact | string | nullable |
| email | string | nullable |
| phone | string | nullable |
| lead_time_days | integer | nullable |

### FORMULA — `formula_versions`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| formula_id | bigint | FK → formulas | |
| version | integer | | Auto-increment per formula |
| materials_snapshot | json | | Full material composition at version |
| cost_per_ml | decimal(10,2) | | Calculated at version creation |
| created_by | bigint | FK → users | |
| notes | text | nullable | |

### FORMULA — `material_ifra_limits`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| material_id | bigint | FK → materials | |
| category | string | | IFRA category (1-12) |
| max_percent | decimal(8,4) | | Maximum allowed % |

### FORMULA — `material_supplier`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| material_id | bigint | FK → materials | |
| supplier_id | bigint | FK → suppliers | |
| harga_beli | decimal(10,2) | | Price per unit from this supplier |
| is_primary | boolean | | Default supplier |

### PRODUCTION — `batch_productions`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| formula_id | bigint | FK → formulas | |
| formula_version_id | bigint | FK → formula_versions | |
| batch_volume_ml | decimal(10,2) | | |
| jumlah_unit | integer | | Bottles produced |
| status | string | planned/in_progress/completed/cancelled | |
| operator_id | bigint | FK → users, nullable | |
| scheduled_date | date | nullable | |
| completed_at | timestamp | nullable | |
| notes | text | nullable | |

### PRODUCTION — `batch_material_usages`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| batch_production_id | bigint | FK → batch_productions | |
| material_id | bigint | FK → materials | |
| quantity_used | decimal(10,2) | | In material's unit |
| cost | decimal(10,2) | | Cost at time of production |

### PRODUCTION — `stock_movements`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| material_id | bigint | FK → materials | |
| type | string | in/out/adjustment | |
| quantity | decimal(10,2) | | Positive for in, negative for out |
| reference_type | string | batch_production/purchase/adjustment | |
| reference_id | bigint | nullable | Polymorphic |
| notes | text | nullable | |

### PRODUCTION — `produks` (existing, extend — finished goods)
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| is_active | boolean | default true | Show in storefront |
| scent_family_tags | json | nullable | For filtering |
| description_html | text | nullable | Rich description for storefront |
| images | json | nullable | Array of image paths |
| is_pre_order | boolean | default false | |
| pre_order_quota | integer | nullable | Max slots |
| is_sample_available | boolean | default false | |
| sample_price | decimal(10,2) | nullable | |
| sample_volume_ml | decimal(5,2) | nullable | |

### STOREFRONT — `product_variants`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| produk_id | bigint | FK → produks | |
| name | string | e.g. "30ml", "50ml", "Sample 2ml" | |
| volume_ml | decimal(5,2) | | |
| price | decimal(10,2) | | |
| stock | decimal(10,2) | | |
| is_active | boolean | default true | |

### STOREFRONT — `orders`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| user_id | bigint | FK → users | |
| order_number | string | unique | Auto-generated |
| status | string | pending/paid/production/shipping/delivered/cancelled | |
| subtotal | decimal(12,2) | | |
| shipping_cost | decimal(10,2) | default 0 | |
| tax | decimal(10,2) | default 0 | |
| total | decimal(12,2) | | |
| notes | text | nullable | |
| paid_at | timestamp | nullable | |
| shipped_at | timestamp | nullable | |

### STOREFRONT — `order_items`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| order_id | bigint | FK → orders |
| product_variant_id | bigint | FK → product_variants |
| quantity | integer | |
| price_per_unit | decimal(10,2) | |
| subtotal | decimal(12,2) | |

### STOREFRONT — `pre_orders`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| order_id | bigint | FK → orders | |
| product_variant_id | bigint | FK → product_variants | |
| slot_number | integer | | Position in pre-order queue |
| status | string | waiting/fulfilled/cancelled | |

### CRM — `customer_profiles`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| user_id | bigint | FK → users | unique |
| total_spent | decimal(12,2) | default 0 | |
| total_orders | integer | default 0 | |
| last_order_at | timestamp | nullable | |
| preferred_scent_families | json | nullable | Array of scent families |
| notes | text | nullable | |

### CRM — `abandoned_carts`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| user_id | bigint | FK → users | |
| cart_data | json | | Snapshot of cart items |
| total | decimal(12,2) | | |
| status | string | pending/recovered/lost | |
| abandoned_at | timestamp | | |
| email_sent_at | timestamp | nullable | |
| wa_sent_at | timestamp | nullable | |
| recovered_at | timestamp | nullable | |

### MARKETING — `campaigns`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| name | string | |
| type | string | email/wa/both |
| trigger_event | string | order_placed/cart_abandoned/customer_registered/manual |
| status | string | draft/active/paused/completed |
| segment_filter | json | nullable |

### MARKETING — `campaign_sequences`
| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint | PK | |
| campaign_id | bigint | FK → campaigns | |
| delay_hours | integer | | Delay after trigger |
| channel | string | email/wa | |
| subject | string | nullable | Email subject |
| message | text | | Email body or WA message |
| order | integer | | Sequence order |

### MARKETING — `referral_links`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| code | string | unique |
| commission_percent | decimal(5,2) | default 10 |
| total_clicks | integer | default 0 |
| total_conversions | integer | default 0 |
| total_commission_earned | decimal(12,2) | default 0 |

### MARKETING — `referral_conversions`
| Column | Type | Constraints |
|--------|------|------------|
| id | bigint | PK |
| referral_link_id | bigint | FK → referral_links |
| referred_user_id | bigint | FK → users |
| order_id | bigint | FK → orders, nullable |
| commission_amount | decimal(10,2) | |
| status | string | pending/paid |

---

## 4. Indexing Strategy

| Table | Index | Columns | Type | Reason |
|-------|-------|---------|------|--------|
| orders | idx_orders_user_id | user_id | BTREE | Customer order history |
| orders | idx_orders_status | status | BTREE | Filter by status |
| orders | idx_orders_created_at | created_at | BTREE | Analytics aggregation |
| order_items | idx_order_items_order_id | order_id | BTREE | Order detail |
| batch_productions | idx_batch_formula_id | formula_id | BTREE | Batch history per formula |
| batch_productions | idx_batch_status | status | BTREE | Filter active batches |
| stock_movements | idx_stock_material_id | material_id | BTREE | Stock history per material |
| stock_movements | idx_stock_reference | reference_type, reference_id | BTREE | Polymorphic lookup |
| abandoned_carts | idx_abandoned_status | status | BTREE | Recovery workflow |
| campaigns | idx_campaigns_status | status | BTREE | Active campaigns |
| referral_links | idx_referral_code | code | UNIQUE | Lookup by code |
| product_variants | idx_variant_produk_id | produk_id | BTREE | Variants per product |
| formula_versions | idx_fv_formula_id | formula_id | BTREE | Version history |
| material_ifra_limits | idx_ifra_material_id | material_id | BTREE | IFRA lookup |

---

## 5. Migration Plan

| # | Migration | Table | Module |
|---|----------|-------|--------|
| 1 | create_roles_table | roles | Core |
| 2 | add_role_and_phone_to_users | users | Core |
| 3 | create_suppliers_table | suppliers | Formula |
| 4 | add_supplier_fields_to_materials | materials | Formula |
| 5 | create_material_supplier_table | material_supplier | Formula |
| 6 | create_material_ifra_limits_table | material_ifra_limits | Formula |
| 7 | create_formula_versions_table | formula_versions | Formula |
| 8 | create_batch_productions_table | batch_productions | Production |
| 9 | create_batch_material_usages_table | batch_material_usages | Production |
| 10 | create_stock_movements_table | stock_movements | Production |
| 11 | add_storefront_fields_to_produks | produks | Storefront |
| 12 | create_product_variants_table | product_variants | Storefront |
| 13 | create_orders_table | orders | Storefront |
| 14 | create_order_items_table | order_items | Storefront |
| 15 | create_pre_orders_table | pre_orders | Storefront |
| 16 | create_customer_profiles_table | customer_profiles | CRM |
| 17 | create_abandoned_carts_table | abandoned_carts | CRM |
| 18 | create_campaigns_table | campaigns | Marketing |
| 19 | create_campaign_sequences_table | campaign_sequences | Marketing |
| 20 | create_referral_links_table | referral_links | Marketing |
| 21 | create_referral_conversions_table | referral_conversions | Marketing |

---

## 6. Seed Data

| Seeder | Purpose |
|--------|---------|
| RoleSeeder | Create roles: owner, produksi, cs |
| AdminUserSeeder | Create default owner account |
| SupplierSeeder | Sample suppliers |
| MaterialCategorySeeder | Initial sub-categories |
