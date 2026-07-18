# Backend Structure

> **Status:** DATA FILE — Struktur backend codebase.

---

## Directory Layout

```
├── app/
│   ├── Actions/Fortify/     # Fortify auth actions
│   ├── Concerns/            # Shared traits
│   ├── Console/             # Artisan commands
│   ├── Enums/               # JenisKonsentrasi, MaterialType
│   ├── Http/Controllers/    # Base Controller only (Livewire-driven)
│   ├── Livewire/            # Livewire components
│   ├── Models/              # Eloquent models (7 models)
│   └── Providers/           # Service providers
├── bootstrap/
├── config/
├── database/
│   ├── migrations/          # 18 migration files
│   ├── factories/
│   └── seeders/
├── public/
├── resources/
│   └── views/               # Blade templates
├── routes/
│   ├── web.php              # Main routes
│   ├── settings.php         # Settings routes
│   └── console.php
├── storage/
├── tests/
└── vendor/
```

## Architecture Pattern

**Livewire monolith.** No API layer. Server-rendered full-page Livewire components. Business logic lives in Models (calculated attributes) and Livewire components (form handling, validation).

## Auth Flow

Fortify handles: login, register, password reset, email verification, 2FA. Passkeys (WebAuthn) via `@laravel/passkeys` package for passwordless auth.
