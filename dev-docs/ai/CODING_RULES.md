# CODING_RULES

> **Status:** DATA FILE — Aturan coding spesifik project yang diekstrak dari codebase.

---

## Part A — Backend Rules

### 1) Module and Route Conventions

- Routes defined in `routes/web.php` and `routes/settings.php`
- All authenticated routes use `Route::livewire()` for full-page components
- Route naming: `{resource}.{action}` (e.g., `materials.index`, `materials.create`)
- Auth routes grouped under `auth` + `verified` middleware
- Settings routes split: profile (no verified), appearance/security (verified)

### 2) Authorization Conventions

- Auth via `auth` and `verified` middleware on route groups
- Laravel Fortify handles authentication (login, register, password reset, 2FA)
- Passkeys (WebAuthn) via `@laravel/passkeys`
- No RBAC/permission system yet

### 3) Data and Transaction Conventions

- No explicit DB transaction wrappers
- Form validation handled by Livewire component validation
- No Form Request classes — validation inline in Livewire
- No API Resource / DTO pattern

### 4) Import Conventions

- No import/export functionality yet

---

## Part B — Frontend Rules

### 5) UI Conventions

- Full-page Livewire components in `resources/views/pages/` with `?` prefix convention
- Layouts: `app` (authenticated), `auth` (guest) — each with sub-templates
- Flux UI components used throughout
- TailwindCSS v4 utility classes
- Design system defined in `DESIGN.md` — warm atelier palette
- Custom Flux icons in `resources/views/flux/icon/`
- No JavaScript framework (Vue/React) — all reactivity via Livewire

### 6) Testing Conventions

- Framework: Pest v4
- Location: `tests/`
- Naming: Test files mirror source structure
- CI: `composer test` runs lint + phpstan + pest

### 7) Documentation Conventions

- No PHPDoc requirement observed
- Minimal inline comments
- Design documentation in `DESIGN.md` at project root
