# API Flow

> **Status:** DATA FILE — Flow request API/web. Project ini Livewire monolith (no REST API).

---

## Request Flow

```
Browser → Route (web.php/settings.php) → Livewire Component
  → Livewire lifecycle (mount, render, actions, validation)
    → Eloquent queries
    → Blade view render with Flux + TailwindCSS
  → HTML response sent to browser
  → Livewire JavaScript handles subsequent AJAX interactions
```

## Livewire Component Lifecycle

1. **Initial load:** Full page render via `Route::livewire()`
2. **User interaction:** Click/submit → Livewire AJAX request
3. **Server:** Component method called, state updated, re-render
4. **Response:** Updated HTML sent, Livewire JS morphs DOM

## Auth Flow

| URL | Component | Middleware |
|-----|-----------|------------|
| `/login` | Fortify | guest |
| `/register` | Fortify | guest |
| `/dashboard` | `pages::dashboard` | auth, verified |
| `/materials` | `pages::materials-index` | auth, verified |
| `/formulas` | `pages::formulas-index` | auth, verified |
| `/products` | `pages::products-index` | auth, verified |
| `/settings/profile` | `pages::settings.profile` | auth |
| `/settings/security` | `pages::settings.security` | auth, verified, password.confirm |
