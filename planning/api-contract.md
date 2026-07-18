# API Contract — Ventura of Scent

> **Status:** PLANNING — Endpoint untuk storefront (customer-facing). Admin menggunakan Livewire full-page (no API).

---

## Storefront API (Customer)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/products` | No | Product catalog |
| GET | `/api/products/{id}` | No | Product detail + variants |
| GET | `/api/products/{id}/pyramid` | No | Fragrance pyramid data |
| POST | `/api/cart/add` | Yes | Add to cart |
| GET | `/api/cart` | Yes | Get cart |
| POST | `/api/cart/remove/{id}` | Yes | Remove from cart |
| POST | `/api/checkout` | Yes | Place order |
| GET | `/api/orders` | Yes | Order history |
| GET | `/api/orders/{id}` | Yes | Order detail |
| POST | `/api/pre-order/check` | No | Check pre-order availability |
| POST | `/api/referral/claim` | Yes | Claim referral reward |
