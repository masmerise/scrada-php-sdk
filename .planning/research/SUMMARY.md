# Project Research Summary

**Project:** Scrada PHP SDK — POS Add Receipts endpoint
**Domain:** PHP SDK feature addition (subsequent milestone, v0.3.0)
**Researched:** 2026-03-25
**Confidence:** HIGH (stack/architecture/pitfalls) / MEDIUM (features — API spec not directly accessible)

## Executive Summary

This is a bounded feature addition to an established PHP SDK (v0.2.0), not a greenfield project. The stack, toolchain, and architectural conventions are fully locked. The deliverable is a single new domain module (`Pos/`) that exposes `$scrada->pos->addReceipts(CompanyId, CashBookId, AddReceipts): true` through the existing Saloon-based HTTP connector. All patterns to follow are directly observable in the codebase — `CashBookResource`, `UpdateCashBook`, and `CouldNotUpdateCashBook` are the canonical templates. No new dependencies are needed.

The recommended approach is strictly convention-following: create primitive value objects first (`LastLineId`, `VatCategoryId`, `PaymentMethodId`, `Amount`), compose them into sub-DTOs (`ReceiptLine`, `Payment`) and a root DTO (`AddReceipts`), wire a `AddReceiptsRequest` using `HasJsonBody`, define `CouldNotAddReceipts`, expose everything through a `PosResource`, and register it on `Scrada.php`. Build order matters — primitives before DTOs, DTOs before request, request and failure before resource, resource before wiring, wiring before tests. This order is enforced by PHP's class dependency graph.

The primary risks are not architectural but specification-driven. Three decisions cannot be resolved without the Scrada API spec: (1) whether receipt amounts are transmitted as integer cents or decimal strings (critical for fiscal accuracy in Belgian bookkeeping), (2) whether the endpoint returns a response body requiring a mapping trait, and (3) whether the receipt date field requires a full `DateTime` or a calendar `Date`. These gaps must be resolved against the API spec before implementation begins on the respective value objects. If the spec is not available before implementation starts, flag these as open decisions and stub with conservative defaults.

## Key Findings

### Recommended Stack

The stack is fully established. All packages are locked via Composer and no additions are required. The feature uses PHP 8.5's `readonly` constructors and named arguments throughout. Saloon v3.15.0 provides the `HasBody`/`HasJsonBody` traits for the POST request class. `webmozart/assert` handles runtime validation inside all new value objects. Laravel Pint enforces code style and PHPStan (level configured in `phpstan.neon`) enforces types — both must pass before the feature is considered done.

**Core technologies:**
- PHP ~8.5: Runtime — `final readonly` classes, named arguments, `never` return type
- saloonphp/saloon v3.15.0: HTTP connector, request/response abstraction — provides `HasBody`, `HasJsonBody`, `Method::POST`
- webmozart/assert ^1.12: Value object validation — `Assert::uuid()`, `Assert::notEmpty()`, etc.
- psr/simple-cache ^3.0: Rate limit cache store — already wired, no new usage required
- phpunit/phpunit 12.5.14: Integration test runner — attribute-based (`#[Test]`, `#[Group('pos')]`), real API credentials
- phpstan/phpstan 2.1.43: Static analysis — catches missing `@throws`, type errors, wrong return types
- laravel/pint v1.29.0: Code formatter — run with `composer format`

### Expected Features

The full feature set for this milestone is well-defined. No speculative features belong in v0.3.0.

**Must have (table stakes):**
- `PosResource` with `addReceipts(CompanyId, CashBookId, AddReceipts): true` — primary deliverable
- `AddReceipts` root DTO with `parameters()` static constructor and `toArray()` serialiser
- `AddReceiptsRequest` — Saloon POST request, `HasBody` + `HasJsonBody`, correct endpoint resolution
- `CouldNotAddReceipts` — typed failure exception extending `ValidationException`
- Primitive value objects: `LastLineId`, `VatCategoryId`, `PaymentMethodId` (UUID wrappers)
- `Amount` value object (fiscal precision — do not reuse `Balance::fromFloat()` without verifying API format)
- `Scrada::$pos` public property wired in `Scrada.php`
- Integration test: `add_receipt_to_cash_book()` in a `PosTests` trait, `#[Group('pos')]`

**Should have (quality differentiators):**
- `@throws CouldNotAuthenticate`, `@throws CouldNotAddReceipts`, `@throws UnknownException` on resource method — enables caller static analysis
- Descriptive docblocks on all DTO constructor fields, especially `lastLineID` (non-obvious workflow dependency)
- `ReceiptLine`, `Payment` sub-DTOs if the API payload is nested (follow ARCHITECTURE.md build order)

**Conditional (v1.x — requires API spec confirmation):**
- `MapsReceipts` mapping trait and response value object — only if POST returns a non-empty body
- `DateTime` value object in `Core/Type/Primitive/` — only if receipt date requires time-of-day

**Defer (v2+):**
- Batch receipt submission helpers — premature without understanding `lastLineID` sequencing
- Other POS endpoints (daily receipts ledger, Peppol, centralisation) — explicitly out of scope
- Laravel service provider — belongs in the separate Laravel adapter package

### Architecture Approach

The architecture is a clean domain module drop-in. The `Pos/` module follows an identical structure to `CashBook/` and `Company/`. All new code lives under `src/Pos/` with a single additive change to `src/Scrada.php`. The module is isolated: `Pos/` depends on `Core/` and references `CompanyId`/`CashBookId` from their respective modules, but no existing modules depend on `Pos/`. The `@internal` boundary is maintained — callers access only the `Scrada` facade and `Pos/Type/` value objects.

**Major components:**
1. `Scrada.php` — entry point; add `public readonly PosResource $pos` and instantiate in constructor
2. `PosResource` — extends `ScradaResource`; exposes `addReceipts()`, calls `$this->send()`, returns `true`
3. `AddReceiptsRequest` — Saloon Request; `HasBody` + `HasJsonBody`; `defaultBody()` calls `$data->toArray()`
4. `AddReceipts` DTO + sub-DTOs — `final readonly` with `parameters()` / `toArray()`; nested serialisation is recursive
5. Primitive value objects — one file per scalar; UUID wrappers use `Assert::uuid()`; `Amount` needs careful float/int decision
6. `CouldNotAddReceipts` — one-line class extending `ValidationException`; passed as `onFailure:` class-string to `send()`
7. `PosTests` trait — integration test; real API; `#[Group('pos')]`; requires `lastLineID` from a prior GET

### Critical Pitfalls

1. **Amount representation (float vs. integer cents)** — Do not reuse `Balance::fromFloat()`. Verify from the API spec whether the endpoint expects integer cents (`1010` for €10.10) or a decimal string (`"10.10"`). Belgian fiscal systems require cent-level precision; PHP float will produce binary rounding errors. Design the `Amount` value object before writing the DTO.

2. **Null vs. omit in `toArray()`** — The `UpdateCashBook` pattern sends null values intentionally (null = keep existing). Receipt submission is append-only; null optional fields should be stripped with `array_filter`. Conflating these semantics will produce unexpected API validation errors that are hard to diagnose.

3. **Line items serialising as JSON object instead of array** — After any `array_filter` or `array_map` on receipt lines, PHP may produce non-sequential keys, causing `json_encode` to output `{"0": {...}}` instead of `[{...}]`. Always wrap line-item arrays in `array_values()` in `toArray()`.

4. **Missing `Scrada.php` wiring** — PHPStan will not catch an unregistered resource. Wire `$this->pos = new PosResource($this->client)` in `Scrada.php` as the very first implementation task, before writing any resource logic. This ensures the omission is caught immediately at class instantiation time.

5. **Missing `@throws` annotations** — Create `CouldNotAddReceipts` and add `@throws CouldNotAddReceipts` to the resource method docblock at the same time as the `onFailure:` argument. Run PHPStan after each new resource method — it will catch undeclared throws.

## Implications for Roadmap

Based on research, the build order is dictated by PHP class dependency resolution. There is one logical implementation phase for this milestone, subdivided by build order layers.

### Phase 1: API Spec Confirmation (Pre-Implementation Gate)

**Rationale:** Three decisions block correct implementation of value objects and the request class: amount format, response body existence, and date/datetime requirement. These must be resolved before any code is written to avoid rework on the most foundational layer (value objects).
**Delivers:** Confirmed answers to four open questions from FEATURES.md
**Addresses:** Pitfalls 1 (amount), 2 (null/omit), 5 (date format), and the conditional `MapsReceipts` decision
**Avoids:** Rewriting `Amount`, `AddReceipts::toArray()`, or the resource return type after tests reveal a mismatch
**Research flag:** This is not a `/gsd:research-phase` code task — it is a manual spec review gate. The developer must provide the API spec (or a cURL response from the test environment) before Phase 2 begins.

### Phase 2: Primitive Value Objects

**Rationale:** All DTOs, requests, and resources depend on primitives. No other code can be written correctly until the primitive layer is complete and passing PHPStan.
**Delivers:** `LastLineId`, `VatCategoryId`, `PaymentMethodId` (UUID wrappers); `Amount` (format confirmed in Phase 1); optionally `DateTime` if required
**Uses:** `webmozart/assert`, existing `Core/Type/Primitive/` pattern
**Implements:** Architecture build step 1
**Avoids:** Pitfall 1 (amount precision), Pitfall 5 (date format)
**Research flag:** Standard patterns — no `/gsd:research-phase` needed. Follow existing UUID wrapper pattern exactly.

### Phase 3: DTOs and Request Class

**Rationale:** `AddReceipts` DTO (and any sub-DTOs) depend on primitives from Phase 2. `AddReceiptsRequest` depends on the DTO.
**Delivers:** `ReceiptLine`, `Payment` sub-DTOs (if needed); `AddReceipts` root DTO with `toArray()`; `AddReceiptsRequest` with `HasJsonBody`
**Uses:** `HasBody`, `HasJsonBody` from Saloon; `Method::POST`; `CompanyId`, `CashBookId` from existing modules
**Implements:** Architecture build steps 2, 3, 4
**Avoids:** Pitfall 2 (null vs. omit), Pitfall 3 (array vs. object serialisation)
**Research flag:** Standard patterns — follow `UpdateCashBook` + `UpdateCashBookRequest` exactly.

### Phase 4: Failure Exception, Resource, and Facade Wiring

**Rationale:** The exception class and `Scrada.php` wiring should be created before the resource method body — not after — so that the omission is caught at class instantiation, not at test time.
**Delivers:** `CouldNotAddReceipts`; `PosResource` with `addReceipts()` method; `Scrada::$pos` wired
**Uses:** `ValidationException` from `Core`; `ScradaResource::send()`
**Implements:** Architecture build steps 5, 6, 7
**Avoids:** Pitfall 4 (missing wiring), Pitfall 6 (missing `@throws`)
**Research flag:** Standard patterns — no `/gsd:research-phase` needed.

### Phase 5: Integration Test

**Rationale:** Tests depend on a fully wired resource and require real API credentials. PHPUnit, PHPStan, and Pint all run as a final verification gate.
**Delivers:** `PosTests` trait with `add_receipt_to_cash_book()` test; `#[Group('pos')]`; updated `tests/.env.example`
**Uses:** PHPUnit 12 attribute-based tests; `$_ENV` credentials; real Scrada test API
**Implements:** Architecture build step 8
**Avoids:** Credential exposure; production environment during tests
**Research flag:** Standard patterns — follow `CashBookTests.php` and `CompanyTests.php` exactly.

### Phase Ordering Rationale

- **Spec before code:** The open questions in FEATURES.md (amount format, response body, date type) are all value-object decisions. Getting these wrong requires rewriting the foundational layer. A 30-minute spec review gate saves hours of rework.
- **Primitives before DTOs:** PHP will not compile a DTO that references a non-existent value object class. The dependency graph enforces this order.
- **Exception before resource:** Creating `CouldNotAddReceipts` first ensures PHPStan catches any `onFailure:` class-string mismatch immediately.
- **Wiring before logic:** Adding `$this->pos = new PosResource($this->client)` to `Scrada.php` before `PosResource` exists produces an immediate fatal error — a deliberate reminder that the resource class must be created.
- **One real change to existing files:** Only `src/Scrada.php` is modified. Everything else is new files under `src/Pos/`. This eliminates regression risk for existing `cashBook` and `company` resource consumers.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 1 (Spec confirmation):** Not a code research task — requires the developer to provide the API spec or a live cURL trace. The amount format, response body shape, and exact endpoint URL are all unknown. This is the highest-risk gap.

Phases with standard patterns (skip `/gsd:research-phase`):
- **Phase 2 (Primitive value objects):** Exact pattern established in `CashBookId`, `CompanyId`, `Balance`, `Iban`
- **Phase 3 (DTOs and Request):** Exact pattern established in `UpdateCashBook` and `UpdateCashBookRequest`
- **Phase 4 (Exception, Resource, Wiring):** Exact pattern established in `CouldNotUpdateCashBook`, `CashBookResource`, `Scrada.php`
- **Phase 5 (Tests):** Exact pattern established in `CashBookTests.php` and `CompanyTests.php`

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | All packages version-locked; confirmed via `composer show`; no ambiguity |
| Features | MEDIUM | Core feature set is clear; four open questions require API spec input; table stakes list is complete |
| Architecture | HIGH | Derived from direct codebase analysis of v0.2.0; no inference required |
| Pitfalls | HIGH (code) / MEDIUM (ecosystem) | Code pitfalls confirmed by direct analysis; Belgian fiscal requirements confirmed by external sources |

**Overall confidence:** HIGH for implementation approach; MEDIUM for spec-dependent decisions (amount format, response body, date type, exact endpoint URL)

### Gaps to Address

- **Amount format:** Is the API expecting integer cents (`1010`) or decimal string (`"10.10"`) for financial amounts? Resolve by reading the API spec or sending a test cURL request before implementing `Amount`. Do not default to `Balance::fromFloat()`.
- **Response body:** Does `POST .../pos/receipts` return `204 No Content`, `200 {}`, or `200 {receiptId: "..."}` or similar? If non-empty, a `MapsReceipts` trait and return value object are required; if empty, `true` suffices. Resolve from spec before writing the resource method signature.
- **Exact endpoint URL:** Research found `company/{companyId}/cashBook/{cashBookId}/pos` as a placeholder. Confirm the exact path from the API spec. The HTTP method (POST vs. PUT) must also be confirmed — Scrada uses PUT for `UpdateCashBook`.
- **`lastLineID` type:** Is it a UUID string, an integer sequence, or something else? Determines the value object wrapper and validation rule.
- **Date vs. DateTime:** Does the receipt submission date require time-of-day (`YYYY-MM-DDTHH:mm:ss`) or is a calendar date (`YYYY-MM-DD`) sufficient? If datetime is required, a new `DateTime` primitive is needed in `Core/Type/Primitive/`.

## Sources

### Primary (HIGH confidence)
- Scrada PHP SDK v0.2.0 codebase — all architecture patterns, conventions, and build order
- `composer show` output — locked package versions (saloonphp/saloon v3.15.0, phpunit v12.5.14, phpstan v2.1.43, pint v1.29.0)
- `src/CashBook/Update/UpdateCashBookRequest.php` — canonical POST/HasJsonBody request pattern
- `src/CashBook/Update/UpdateCashBook.php` — canonical input DTO pattern
- `src/Core/Http/ScradaResource.php` — `send()` method and error mapping contract
- `src/Core/Type/Collection.php` — typed collection base; array key serialisation behaviour
- `src/CashBook/Type/Primitive/Balance.php` — float-based money representation (confirms float precision risk)

### Secondary (MEDIUM confidence)
- https://support.scrada.be/nl/support/solutions/articles/103000132277-scrada-api — confirms `lastLineID` requirement, sign rules, daily receipts concept
- https://docs.saloon.dev/the-basics/request-body-data/json-body — `HasBody`/`HasJsonBody` pattern confirmed current for Saloon v3
- WebSearch: Belgian fiscal receipt requirements — confirms cent-precision requirement for VAT reporting

### Tertiary (LOW confidence — require spec validation)
- Inferred endpoint URL `company/{companyId}/cashBook/{cashBookId}/pos/receipts` — placeholder only
- Assumed `true` return type — inferred from similar write endpoints; unconfirmed against actual API response
- Assumed receipt lines are a nested array in the payload — unconfirmed without API spec

---
*Research completed: 2026-03-25*
*Ready for roadmap: yes*
