# Feature Research

**Domain:** PHP SDK — POS receipt submission endpoint (Scrada Add Receipts)
**Researched:** 2026-03-25
**Confidence:** MEDIUM

---

## Context

This research is scoped to a single endpoint: adding POS receipts (dagontvangsten/daily receipts) to a Scrada
cash book. The SDK is not a new product — it is an established library at v0.2.0 with firm conventions.
Features here are evaluated as "what must the SDK wrapper provide" not "what product features to build."

The Scrada API accepts receipt line submissions scoped to a company and cash book. Based on the API search
results and domain knowledge:

- The endpoint POSTs one or more receipt lines to a daily receipts book
- It requires a `lastLineID` to prevent concurrent write conflicts (optimistic concurrency)
- VAT category and payment method rules are enforced server-side (sign constraints)
- The Belgian context requires tracking VAT categories, payment methods, and amounts

---

## Feature Landscape

### Table Stakes (Users Expect These)

Features any POS integration developer expects when wrapping a receipt submission endpoint. Missing
these makes the SDK unusable or forces callers to drop into raw HTTP.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| `AddReceiptsRequest` class | Every Saloon-based endpoint needs a Request class; it's the entry point | LOW | POST method, `HasJsonBody` trait, resolves to `company/{id}/cashBook/{id}/receipts` or similar |
| `AddReceipts` DTO (parameter object) | Existing pattern: all write operations have a typed DTO; callers need type safety when assembling receipt data | MEDIUM | Requires value objects for all typed fields (amounts, VAT category IDs, payment method IDs, lastLineID) |
| `CouldNotAddReceipts` failure exception | Every mutation endpoint has a dedicated typed failure; callers catch domain exceptions not raw HTTP errors | LOW | Extends `ValidationException`, same pattern as `CouldNotUpdateCashBook` |
| `PosResource` (or `ReceiptResource`) class | Existing pattern: each domain area has a Resource class exposing public methods | LOW | Must be wired into `Scrada` facade as a public property |
| Registration on `Scrada` facade | Callers use `$scrada->pos->add(...)` or equivalent — consistency with `$scrada->cashBook` and `$scrada->company` | LOW | One-line addition in `Scrada.php` |
| `lastLineID` field support | API requires it to prevent concurrent writes; omitting it means submissions will fail or silently overwrite | MEDIUM | Value object `LastLineId` wrapping UUID; callers must first fetch journal to get this value |
| Receipt amount / monetary value object | Financial amounts must not be raw floats; existing `Balance` value object may be re-used or a new `Amount` value object needed | LOW | Determine if `Balance`'s non-negative constraint fits receipt amounts (receipts may include negative correction lines) |
| VAT category ID value object | VAT categories are UUIDs configured in Scrada; callers pass the UUID, SDK wraps it for type safety | LOW | `VatCategoryId` wrapping UUID string with UUID validation |
| Payment method ID value object | Payment methods (cash, debit card, etc.) are also UUID-referenced; same pattern as VAT category | LOW | `PaymentMethodId` wrapping UUID string |
| `true` return type on success | Existing pattern: mutations return `true` on success (no response body to map); callers get a clean boolean | LOW | Consistent with `CashBookResource::update()` and `CompanyResource::update()` |
| Integration test for `add()` | Existing tests hit the real API; test completeness is expected in this project | MEDIUM | Needs `CASH_BOOK_ID` and valid `lastLineID` from a prior `get` call; may need a fixture journal |

### Differentiators (Competitive Advantage)

Features that go beyond mechanical API mapping and make the SDK more valuable to use.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Negative amount support with explicit sign handling | Belgian receipts include correction lines (returns, voids) which may be negative; a dedicated `Amount` VO that accepts negative values — distinct from the always-positive `Balance` — prevents callers from hitting server-side sign validation errors with confusing messages | MEDIUM | If `Balance` is reused it must be relaxed or a new `Amount` VO created; server enforces sign rules per VAT/payment method config |
| PHPDoc `@throws` coverage on resource method | Existing resource methods are fully annotated; callers relying on static analysis (PHPStan) benefit from knowing which exceptions to catch | LOW | Three exception classes: `CouldNotAuthenticate`, `CouldNotAddReceipts`, `UnknownException` |
| `MapsReceipts` mapping trait (if response body exists) | If the endpoint returns receipt data (e.g., assigned IDs), a mapping trait keeps the response-to-VO logic isolated and testable — consistent with `MapsCashBooks` | MEDIUM | Only relevant if the API returns a body; if it returns 204/200 with empty body, this is not needed |
| Descriptive docblock on DTO constructor fields | Existing DTOs explain each field's semantics in inline docblocks (see `UpdateCashBook`); receipt fields like `lastLineID` have non-obvious semantics worth documenting | LOW | Zero runtime cost, high value for callers who don't read API docs |

### Anti-Features (Commonly Requested, Often Problematic)

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| Auto-fetching `lastLineID` inside `add()` | Callers may want a "just add it" method that internally GETs the journal to get `lastLineID` | Hides a round-trip network call; violates single-responsibility of the request; makes testing harder; also couples this to a "get journal" endpoint that isn't in scope | Require callers to pass `LastLineId` explicitly; document the two-step flow in docblocks |
| Batch submission helper | POS integrations may want to submit many receipts at once in a loop | The Scrada API likely expects sequential `lastLineID` chaining (each submission increments it); a "batch" abstraction would need to manage that state, which is brittle and hard to test | Keep `add()` as a single-line submission; callers manage their loop |
| Response body value objects if no body returned | It may seem "complete" to always return a typed response object | If the API returns an empty 200/204, mapping an empty object wastes complexity and misleads callers into thinking there's data | Return `true` like all other mutation endpoints; revisit only if the API specification confirms a non-empty response body |
| `PosReceipt` aggregate (combining line + metadata) | Seems helpful to combine all receipt data in one object | Without knowing the full API spec, premature aggregation risks mismatching the API payload structure | Create value objects field-by-field from the spec; let the DTO flatten them into `toArray()` exactly as required |
| Laravel-specific helper | Some SDK users use Laravel and may want a facade/service provider for the POS resource | Out of scope per PROJECT.md; that belongs in the separate Laravel adapter package | Document in README that the Laravel adapter is a separate package |

---

## Feature Dependencies

```
[AddReceipts DTO]
    └──requires──> [LastLineId value object]
    └──requires──> [VatCategoryId value object]
    └──requires──> [PaymentMethodId value object]
    └──requires──> [Amount value object] (or reuse Balance with sign relaxation)

[AddReceiptsRequest]
    └──requires──> [AddReceipts DTO]
    └──requires──> [CompanyId value object]    (already exists)
    └──requires──> [CashBookId value object]   (already exists)

[PosResource]
    └──requires──> [AddReceiptsRequest]
    └──requires──> [CouldNotAddReceipts exception]

[Scrada facade registration]
    └──requires──> [PosResource]

[Integration test]
    └──requires──> [PosResource]
    └──requires──> [real API credentials + CASH_BOOK_ID env var]   (already exists)
    └──enhances──> [lastLineID obtained from a prior journal GET]
```

### Dependency Notes

- **`AddReceipts` DTO requires new primitive value objects:** `LastLineId`, `VatCategoryId`, and `PaymentMethodId` are all UUID wrappers not yet present in the codebase. They follow the exact pattern of `CashBookId` and `CompanyId`.
- **`Amount` vs `Balance`:** `Balance` asserts `>= 0`, which is inappropriate if receipt correction lines can be negative. This must be resolved against the actual API spec before implementation. If negative amounts are valid, a new `Amount` VO is required rather than relaxing `Balance` (which would be a breaking change).
- **`MapsReceipts` trait is conditional:** Only needed if the endpoint returns a non-empty response body. The spec must be checked. If the endpoint returns 200 with empty body or 204, no mapping trait is needed.
- **`lastLineID` depends on external state:** The caller must fetch the current journal/cash book to obtain `lastLineID` before calling `add()`. This is a workflow dependency, not a code dependency.

---

## MVP Definition

### Launch With (v1 — this milestone)

- [x] `PosResource` with `add(CompanyId, CashBookId, AddReceipts): true` method — the deliverable
- [x] `AddReceipts` parameter DTO with `toArray()` and `parameters()` static constructor
- [x] `AddReceiptsRequest` — POST Saloon request with `HasJsonBody`, correct endpoint resolution
- [x] `CouldNotAddReceipts` — typed failure exception extending `ValidationException`
- [x] Primitive value objects: `LastLineId`, `VatCategoryId`, `PaymentMethodId` (UUID wrappers)
- [x] `Amount` value object (or confirmed reuse of `Balance`) for monetary amounts
- [x] `Scrada::$pos` public property wired in `Scrada.php`
- [x] Integration test: `add_receipt_to_cash_book()` in a `PosTests` trait

### Add After Validation (v1.x)

- [ ] `MapsReceipts` mapping trait and response VO — only if API spec confirms a non-empty response body
- [ ] Additional test cases for error paths (invalid `lastLineID`, sign violation) — once error codes are known

### Future Consideration (v2+)

- [ ] Batch receipt submission helpers — defer until a clear use case exists and API sequencing is understood
- [ ] Other POS endpoints (daily receipts ledger, Peppol, centralisation) — explicitly out of scope for this milestone per PROJECT.md

---

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| `PosResource::add()` with typed params | HIGH | LOW | P1 |
| `AddReceipts` DTO + `toArray()` | HIGH | MEDIUM | P1 |
| `AddReceiptsRequest` | HIGH | LOW | P1 |
| `CouldNotAddReceipts` exception | HIGH | LOW | P1 |
| `LastLineId` / `VatCategoryId` / `PaymentMethodId` VOs | HIGH | LOW | P1 |
| `Amount` value object (negative-safe) | MEDIUM | LOW | P1 |
| `Scrada::$pos` registration | HIGH | LOW | P1 |
| Integration test | MEDIUM | MEDIUM | P1 |
| `MapsReceipts` + response VO | LOW | MEDIUM | P3 (conditional) |
| Batch helper | LOW | HIGH | P3 |

**Priority key:**
- P1: Must have for launch
- P2: Should have, add when possible
- P3: Nice to have, future consideration

---

## Competitor Feature Analysis

No directly comparable PHP SDKs for Scrada exist. The relevant comparisons are internal SDK consistency
patterns.

| Feature | Existing SDK (CashBook) | Existing SDK (Company) | POS Receipts (this milestone) |
|---------|------------------------|----------------------|-------------------------------|
| Typed DTO for write input | `UpdateCashBook` | `UpdateCompany` | `AddReceipts` |
| Typed failure exception | `CouldNotUpdateCashBook` | `CouldNotUpdateCompany` | `CouldNotAddReceipts` |
| Resource class method | `CashBookResource::update()` | `CompanyResource::update()` | `PosResource::add()` |
| Return type on success | `true` | `true` | `true` (assumed; verify spec) |
| Response value object | `CashBook` (GET) | `Company` (GET) | Only if POST returns a body |
| Primitive value objects | `CashBookId`, `Balance`, `Name`, etc. | `CompanyId`, `VatNumber`, etc. | `LastLineId`, `VatCategoryId`, `PaymentMethodId`, `Amount` |

---

## Open Questions (Require API Spec Input)

1. **Does the endpoint return a body?** If yes: what fields? A mapped VO is needed. If no: `true` return suffices.
2. **What is the exact endpoint path?** Likely `company/{companyId}/cashBook/{cashBookId}/receipts` but unconfirmed.
3. **Are receipt amounts always positive?** If correction lines can be negative, `Balance` cannot be reused — a new `Amount` VO is required.
4. **What fields does a single receipt line contain?** From domain knowledge: `lastLineID`, `vatCategoryID`, `paymentMethodID`, `amount`, possibly `date` or `description`. The exact field list must come from the API spec before DTO implementation.
5. **Is `lastLineID` an integer sequence or UUID?** The API snippet mentions "lastLineID" without specifying the type. The existing pattern favours UUID but integer sequences are common in receipt/journal APIs.
6. **Can multiple lines be submitted in one call?** If the endpoint accepts an array of lines, the DTO must support a collection input. If it's one-at-a-time, it's a scalar payload.

---

## Sources

- [Scrada API V1 Documentation](https://www.scrada.be/api-documentation/) — JS-rendered, could not scrape; endpoint fields must be provided manually
- [Scrada API support article](https://support.scrada.be/nl/support/solutions/articles/103000132277-scrada-api) — confirms daily receipts endpoint concept, `lastLineID` requirement, sign rules on VAT/payment methods
- [Scrada Postman collections](https://www.postman.com/scrada) — JS-rendered shell only, no documentation accessible via scraping
- SDK codebase — `src/CashBook/`, `src/Company/`, `src/Core/` — HIGH confidence source for all pattern and convention decisions
- Existing tests — `tests/CashBook/CashBookTests.php`, `tests/Company/CompanyTests.php` — confirmed integration test structure

---

*Feature research for: PHP SDK — Scrada POS Add Receipts endpoint*
*Researched: 2026-03-25*
