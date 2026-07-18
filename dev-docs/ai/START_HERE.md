# START_HERE

> **Status:** GUIDANCE + DATA FILE — Titik masuk pertama untuk AI agent yang onboarding.

---

## Mode Adopsi

Project ini adalah **Adopsi** — codebase existing yang baru diadopsi ke vibe coding workflow. Onboarding analysis 10-step telah dilakukan.

---

## Quick Facts

| Item | Value |
|------|-------|
| Repository | voc-atelier / parfume |
| Project Type | Monolith (standard Laravel) |
| Git Location | Root |
| Stack | Laravel 13 + Livewire 4 + Flux 2 + TailwindCSS 4 |
| DB | SQLite (default) |
| Branch dev | N/A (git not initialized) |
| Branch main | N/A |
| Testing | Pest v4 |

---

## Recommended Reading Order

1. `PROJECT_CONTEXT.md` — Overview sistem dan stack
2. `PROJECT_MENTAL_MODEL.md` — Cara berpikir tentang codebase
3. `MODULE_MAP.md` — Mapping modul ke kode
4. `../architecture/database.md` — Arsitektur database
5. `CURRENT_STATE.md` — Kondisi terkini
6. `TASKS.md` — Task aktif
7. `CODING_RULES.md` — Aturan coding
8. `TECHNICAL_DEBT.md` — Utang teknis

---

## High-Priority Current Work

- **ADS-001** — Setup docs-ai workflow, complete dev-docs onboarding

---

## Safety Notes for Agents

- No git repo initialized — init before first commit
- SQLite in production is not suitable for concurrent access
- Formula concentration calculations need unit tests before production
- No RBAC — all authenticated users have same access
