# AGENTS (AI Working Contract for This Repo)

> **Status:** GUIDANCE FILE — Kontrak kerja AI spesifik untuk repository ini.

---

## 1) Project Identity

| Item | Value |
|------|-------|
| Project Type | Monolith |
| Repo(s) | Root (standard Laravel structure) |
| Git Folder | Root |

---

## 2) Common Rules

### Branch and Git Policy

1. Init git repository at project root before first commit
2. Kerja di `dev` (atau `feat/*`), bukan `main`.
3. Jangan force push.
4. Batch kecil: perubahan jelas, commit jelas.
5. Preflight: `git status && git branch --show-current`

### Change Scope Rules

- Hindari refactor besar tanpa kebutuhan langsung.
- Jangan sentuh konfigurasi sensitif/secret.
- Fokus pada root cause, bukan symptom patch.

### Commit Message Format

Format: `type: judul singkat`

Body (2-5 baris):
- Apa yang dikerjakan
- Kenapa dilakukan
- Dampak perubahan

### Task Reporting

Setiap task selesai + push, buat laporan di `reports/task/YYYY-MM-DD-{task}.md`.

---

## 3) Repo README Maintenance

Setiap milestone / perubahan signifikan selesai, update `README.md` di root project.

---

## 4) Communication Style

- Ringkas, langsung ke hasil.
- Bedakan fakta vs asumsi.
- Gunakan format tabel untuk data terstruktur.
- Selalu sebutkan file path lengkap saat merujuk kode.
