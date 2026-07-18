# Implementation Plan: Formula Enhancements

## Overview
Add material breakdown (gram→% cross-validation) and concentration/bottle recommendation to the Formula module.

## Phase 1: Foundation
- [x] Task 1: JenisKonsentrasi enum + migration
- [x] Task 2: Model changes + computed properties

### Checkpoint: Foundation
- [x] Migration runs clean
- [x] Model stores/retrieves new fields

## Phase 2: UI
- [x] Task 3: Form changes (create + edit) — gram input, concentration, volume, breakdown card, guide card
- [x] Task 4: Show page + route

### Checkpoint: UI
- [x] Forms work end-to-end
- [x] Show page displays breakdown

## Phase 3: Validation & Tests
- [x] Task 5: Server-side cross-validation in save()
- [x] Task 6: Tests

### Checkpoint: Complete
- [x] All tests pass
- [x] Full CRUD flow verified

## Risks
| Risk | Impact | Mitigation |
|------|--------|------------|
| gram nullable causes division by zero | Med | Guard: totalGram=0 → skip breakdown |
| Alpine complexity in form | Low | Follow existing x-data pattern exactly |
