# TECHNICAL_DEBT

> **Status:** DATA FILE — Utang teknis yang ditemukan saat onboarding analysis.

---

## Code Smell / Risky Areas

| Area | Issue | Severity | Recommendation |
|------|-------|----------|----------------|
| SQLite | Production not suitable for concurrent access | High | Configure MySQL/PostgreSQL before production |
| No tests | Formula calculations untested | High | Add Pest tests for Formula::rekomendasiKonsentratMl |
| No service layer | Business logic in Livewire components | Medium | Extract to Service classes when complexity grows |
| No RBAC | No role/permission system | Medium | Implement if multi-user access needed |
| Inline validation | Validation mixed in Livewire components | Low | Move to Form Request when patterns emerge |
| No .git | Repository not version-controlled | High | Initialize git repo |
