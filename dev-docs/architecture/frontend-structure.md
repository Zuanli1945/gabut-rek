# Frontend Structure

> **Status:** DATA FILE — Struktur frontend (Blade + Livewire + Flux).

---

## Directory Layout

```
resources/views/
├── components/              # Shared Blade components
│   ├── app-logo.blade.php
│   ├── app-logo-icon.blade.php
│   ├── auth-header.blade.php
│   ├── auth-session-status.blade.php
│   ├── desktop-user-menu.blade.php
│   ├── passkey-registration.blade.php
│   ├── passkey-verify.blade.php
│   └── placeholder-pattern.blade.php
├── flux/
│   ├── icon/                # Custom Flux icons
│   └── navlist/             # Custom Flux navlist
├── layouts/
│   ├── app.blade.php        # Authenticated layout
│   ├── app/header.blade.php
│   ├── app/sidebar.blade.php
│   ├── auth.blade.php       # Guest layout
│   └── auth/{card,simple,split}.blade.php
├── pages/
│   ├── dashboard.blade.php  # ? prefix = full-page Livewire
│   ├── materials-*.blade.php
│   ├── formulas-*.blade.php
│   ├── products-*.blade.php
│   ├── auth/*               # Login, register, password reset, etc.
│   └── settings/*           # Profile, appearance, security
├── partials/                # Blade partials
├── css/
└── js/
```

## Rendering Pattern

Full-page Livewire components: each page is a Livewire component rendered via `Route::livewire()`. No Vue/React. Reactivity through Livewire's server-side AJAX.

## Styling

TailwindCSS v4 with custom design tokens via `theme.config.json`. Flux 2 components provide pre-built UI elements. Design system in `DESIGN.md`.
