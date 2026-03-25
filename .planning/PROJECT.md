# Scrada PHP SDK — POS Add Receipts

## What This Is

An unofficial PHP SDK for accessing Scrada's API, a Belgian digital bookkeeping platform for POS software ("kassasystemen"). The SDK currently covers Company and CashBook resources using Saloon PHP, with strong typing via value objects and a clear resource/request/failure pattern. This milestone adds the POS Add Receipts endpoint.

## Core Value

Provide a fully-typed, convention-consistent PHP interface for submitting POS receipts to Scrada's API — matching the established SDK patterns exactly.

## Requirements

### Validated

- ✓ Company resource (get, update) — existing
- ✓ CashBook resource (get all, update) — existing
- ✓ Authentication via API key + password — existing
- ✓ Rate limiting with PSR-16 cache — existing
- ✓ Retry with exponential backoff — existing
- ✓ Localization (EN/NL/FR) — existing
- ✓ Environment switching (production/test) — existing
- ✓ Exception-based error handling with ScradaError — existing

### Active

- [ ] POS Add Receipts endpoint (resource, request, value objects, failure exceptions)
- [ ] Full test coverage for POS Add Receipts

### Out of Scope

- Other API endpoints (daily receipts ledger, Peppol, centralisation) — not needed for this milestone
- Laravel adapter changes — separate package
- Breaking changes to existing SDK patterns — extend, don't change

## Context

- **Platform**: Scrada (scrada.be) — Belgian accounting platform for POS/cash register systems
- **Existing SDK**: v0.2.0, PHP ~8.5, built on Saloon PHP v3.14, uses value objects extensively
- **Architecture**: Resource classes (CashBookResource, CompanyResource) with action-specific subdirectories (Get/, Update/, All/) containing Request classes, Failure exceptions, and parameter DTOs
- **API docs**: Available at scrada.be/api-documentation (OpenAPI/ReDoc) — JS-rendered, will need manual spec input during planning
- **Testing**: PHPUnit 12, integration-style tests hitting real API (dotenv for credentials)
- **Quality tools**: PHPStan (static analysis), Laravel Pint (code style)

## Constraints

- **Tech stack**: PHP ~8.5, Saloon PHP v3.14, must follow existing patterns
- **Convention**: All new code must match established SDK conventions (value objects, mapping traits, resource classes, typed failures)
- **API spec**: Endpoint details need to be provided manually (can't scrape JS-rendered docs)

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Follow existing SDK patterns exactly | Consistency is paramount in an SDK — users expect uniform API | — Pending |
| Single endpoint scope | Focused delivery, avoid scope creep | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd:transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd:complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-03-25 after initialization*
