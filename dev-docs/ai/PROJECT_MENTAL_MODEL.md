# PROJECT_MENTAL_MODEL

> **Status:** DATA FILE — AI mengisi pola arsitektur dan mental model codebase.
> **Purpose:** Cara berpikir tentang codebase ini — architectural patterns, request execution, risk hotspots.

---

## Architectural Pattern

**Livewire SPA-like monolith.** Every page is a full-page Livewire component (`pages::*`). No traditional controllers — routing goes directly to Livewire components. Flux UI provides the component library on top of TailwindCSS.

## Request Execution Flow

```
Browser → Route (web.php) → Livewire Component → Eloquent Model → SQLite
                                               → Blade View (rendered)
                                                         → Flux Components
                                                         → TailwindCSS
```

1. Route defines URL → Livewire full-page component
2. Livewire component handles state, validation, DB queries
3. Blade view renders with Flux UI components
4. Vite serves compiled CSS/JS assets

## Key Patterns

- **Full-page components:** All routes use `Route::livewire()` — no partial Livewire embedding
- **Pivot models:** `FormulaMaterial` uses `BelongsToMany` with `using()` for custom pivot
- **Calculated attributes:** `Material::hargaPerSatuan`, `Formula::rekomendasiKonsentratMl` — computed on-the-fly
- **Enum-backed casts:** `JenisKonsentrasi` cast on `Formula.jenis_konsentrasi`
- **No repository layer:** Direct Eloquent in Livewire components (typical Livewire pattern)
- **No service layer:** Business logic in Models and Livewire components
- **Auth:** Laravel Fortify + Passkeys (WebAuthn)

## Risk Hotspots

| Area | Risk | Mitigation |
|------|------|------------|
| SQLite for production | Concurrency, scale | Config ready for MySQL/PostgreSQL |
| Direct Eloquent in Livewire | Tight coupling | Add service layer when complexity grows |
| No tests for core formulas | Formula calculation errors | Add Pest tests for Formula model |
| No queue for production | Email, heavy imports blocking request | Configure queue driver |
