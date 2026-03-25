# Pitfalls Research

**Domain:** PHP SDK — POS Add Receipts endpoint (Scrada Belgian bookkeeping API)
**Researched:** 2026-03-25
**Confidence:** HIGH (codebase direct analysis) / MEDIUM (ecosystem research)

---

## Critical Pitfalls

### Pitfall 1: Null vs. Omit Confusion in Receipt Body Serialization

**What goes wrong:**
The existing `UpdateCashBook::toArray()` sends all keys including null values (e.g., `'name' => null`). For an update endpoint this is deliberate — null means "keep original value." For a receipt submission endpoint, the API likely treats missing keys and explicit null differently: a missing optional field is simply absent, but a null field may trigger a validation error or be rejected as an invalid value.

**Why it happens:**
Developers copy the `UpdateCashBook::toArray()` pattern verbatim for the receipt DTO without checking whether the receipt endpoint follows the same null-means-omit semantic. POS receipt payloads typically have required fields (date, cashBookId, amounts) and strictly optional fields (discount, reference). Sending null for required fields or sending null where the API expects the field to be absent will cause silent failures or unexpected validation errors.

**How to avoid:**
Before implementing `toArray()` for any receipt DTO, confirm from the API spec whether null fields should be serialized as `null` or stripped entirely. If the spec says fields are "optional and if absent are ignored," strip nulls: `array_filter($array, fn($v) => $v !== null)`. If the spec says "null = reset", keep nulls as the update pattern does. The two patterns must not be conflated across different endpoints.

**Warning signs:**
- API returns a 422/400 with "field is required" even though the PHP value object was constructed correctly.
- API silently accepts the request but creates a receipt with zero or missing amounts.
- PHPStan warns about the array return type widening unexpectedly.

**Phase to address:** Implementation phase — in the receipt DTO's `toArray()` method before writing any tests.

---

### Pitfall 2: Money/Amount Representation — Float vs. Integer vs. Scaled Integer

**What goes wrong:**
`Balance` in the existing SDK uses `float` (e.g., `Balance::fromFloat()`). POS receipt line items involve transaction amounts (sale total, VAT amount, discount amount). Representing these as PHP `float` causes binary floating-point precision errors: `0.1 + 0.2 !== 0.3` in PHP. A receipt for €10.10 becomes `10.099999999999999` in JSON, which the API may reject or round incorrectly, producing accounting discrepancies in the Belgian bookkeeping system.

**Why it happens:**
The existing `Balance` value object precedent uses `float`. Developers naturally extend the same primitive for receipt amounts without questioning whether bookkeeping-grade precision requires a different approach. Belgian fiscal systems are strict: every cent must balance.

**How to avoid:**
Check the Scrada API spec for how amounts are transmitted — cents as integer (e.g., `1010` for €10.10) or decimal string (e.g., `"10.10"`). If integers in cents: create a dedicated `Amount` value object that stores values as `int` and converts only for serialization. If decimal string: serialize amounts via `number_format($value, 2, '.', '')` rather than casting float directly. Do not reuse `Balance::fromFloat()` for receipt line-item amounts without verifying the API's accepted format.

**Warning signs:**
- The API returns 422 on amounts that "look correct" but are off by one cent.
- `json_encode` produces trailing decimal digits (e.g., `10.100000000000001`).
- Summing line items in tests produces results that don't equal the receipt total.

**Phase to address:** Implementation phase — the `Amount` (or equivalent) value object must be designed before the receipt DTO.

---

### Pitfall 3: Receipt Line Items Serialized as Associative Array Instead of Indexed Array

**What goes wrong:**
PHP arrays are ordered maps. When a receipt contains line items (e.g., `$items = [0 => $lineItem]`), if the array keys are ever non-sequential (e.g., after an `array_filter` that removes elements), `json_encode` will serialize the array as a JSON object (`{"0": {...}}`) instead of a JSON array (`[{...}]`). The API expects a JSON array. This causes a 400/422 error that is difficult to diagnose because the payload looks correct when printed with `print_r`.

**Why it happens:**
The `Collection` base class in this SDK stores items as a PHP array. As long as items are pushed sequentially the keys stay numeric and `json_encode` produces a JSON array. But if a developer filters the collection before serialization, or if `array_map` is used in a way that preserves keys, the associative-to-array distinction breaks. The existing SDK does not currently face this (GET responses are decoded to objects, PUT bodies have no arrays), making it an easy mistake to overlook.

**How to avoid:**
In the receipt DTO's `toArray()`, always call `array_values()` on any array of line items before returning: `'lines' => array_values(array_map(fn($l) => $l->toArray(), $this->lines))`. Similarly, the `Collection::all()` method already uses the spread (`[...$this->items]`) which preserves keys — prefer `array_values($this->items->all())` when serializing.

**Warning signs:**
- API returns "expected array, got object" style validation error for the line items field.
- `json_encode($body)` output shows `"lines": {"0": {...}}` instead of `"lines": [...]`.
- Tests pass with exactly one line item but fail with filtered subsets.

**Phase to address:** Implementation phase — the receipt DTO `toArray()` and any collection-to-array serialization code.

---

### Pitfall 4: Missing `@throws` Annotations Breaking the Typed Failure Contract

**What goes wrong:**
Every resource method in this SDK declares its `@throws` annotations precisely (e.g., `@throws CouldNotAuthenticate`, `@throws CouldNotUpdateCashBook`). If the receipt `addReceipts()` method is missing the `@throws CouldNotAddReceipts` annotation — or throws an exception that is not declared — PHPStan (level configured in this project) flags violations, and SDK consumers cannot write exhaustive catch blocks without consulting the source.

**Why it happens:**
Developers write the happy path first, add the failure class last, and forget to update the method-level docblock. The `ScradaResource::send()` base method is typed to throw `ValidationException`, but a concrete resource method must re-declare which specific subclass is thrown. Omitting this is easy when copying from another resource without careful review.

**How to avoid:**
Create `CouldNotAddReceipts` (extending `ValidationException`) in `src/Pos/Add/Failure/` before writing the resource method, not after. Add `@throws CouldNotAddReceipts` to the `addReceipts()` docblock at the same time as the `onFailure:` argument is passed to `send()`. Run PHPStan after every new method to catch missing throws annotations before tests run.

**Warning signs:**
- PHPStan reports "Method throws checked exception ... that's not listed in @throws."
- A catch block in user code catches `ValidationException` generically instead of `CouldNotAddReceipts`, indicating the specific type was not surfaced.
- The failure class exists in the wrong namespace (e.g., under `CashBook/` instead of `Pos/`).

**Phase to address:** Implementation phase — failure class and `@throws` annotation must be created before the resource method body.

---

### Pitfall 5: Date Format Mismatch — fromString vs. fromTimestamp for Request Bodies

**What goes wrong:**
`Date::fromTimestamp()` accepts ISO 8601 datetime strings (`YYYY-MM-DDTHH:mm:ss`) from API responses and strips the time component. `Date::fromString()` accepts plain date strings (`YYYY-MM-DD`) for user-supplied input and request bodies. If a developer passes `Date::fromTimestamp()` to a receipt DTO that sends the date back to the API — and the API expects `YYYY-MM-DD` — the serialized value is correct because `toString()` always produces `YYYY-MM-DD`. However, if the receipt API expects a full ISO 8601 datetime (e.g., transaction timestamp including time-of-day for fiscal audit trails), then `Date` cannot represent it and the wrong primitive is reused.

**Why it happens:**
The existing `Date` value object is named generically and works well for calendar dates. POS receipts often require transaction datetime (time-of-day matters for audit logs). A developer reaching for `Date` when the field needs `DateTime` creates a subtle truncation: the time is silently dropped and the API either rejects the format or records all receipts at midnight.

**How to avoid:**
Check the Scrada API spec: does the receipt submission date field require `YYYY-MM-DD` or a full datetime? If a full datetime is required, create a new `DateTime` value object in `src/Core/Type/Primitive/DateTime.php` rather than overloading the existing `Date`. Keep `Date` for calendar-date-only fields, `DateTime` for timestamped events. Do not extend `Date::fromTimestamp()` to output a datetime string — that would break the existing contract.

**Warning signs:**
- API returns a validation error about the date field format.
- Receipt timestamps all show `T00:00:00` in Scrada's dashboard.
- Developer adds a `withTime()` method to `Date` to "fix" it — this is a sign the wrong primitive is being extended.

**Phase to address:** Implementation phase — determine required date format from API spec before choosing or creating the date value object.

---

### Pitfall 6: Registering the New Resource on `Scrada` — Silent Omission

**What goes wrong:**
A `PosResource` class with a working `addReceipts()` method can be fully implemented and tested in isolation, but if a developer forgets to add `public PosResource $pos` as a public property on `Scrada.php` and wire it up in `__construct`, the resource is unreachable by end users. PHPStan will not catch this omission because there is no interface enforcing which resources must be present. The SDK ships "complete" but the new endpoint is inaccessible.

**Why it happens:**
The `Scrada` facade is the entry point but it is not enforced by any interface or abstract base. Adding a resource requires changes in two places: the resource class itself, and the `Scrada.php` constructor. The second step is easy to forget when working in a resource-focused feature branch.

**How to avoid:**
Add the `Scrada.php` wiring as the very first task of the implementation phase (before writing any resource logic). Having `$this->pos = new PosResource($this->client)` fail at runtime (because `PosResource` does not yet exist) is a deliberate reminder. Alternatively, add a smoke test that instantiates `Scrada::authenticate(...)` and asserts `$scrada->pos` is an instance of `PosResource`.

**Warning signs:**
- `$scrada->pos` triggers an "Undefined property" error.
- The feature branch has commits touching only `src/Pos/` with no changes to `src/Scrada.php`.
- PHPStan passes but there is no test that exercises the full call chain through `Scrada`.

**Phase to address:** Implementation phase — wire `Scrada.php` first, before any resource implementation.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Reuse `Balance::fromFloat()` for receipt amounts without checking API format | No new value objects to write | Float precision errors in fiscal amounts; accounting mismatch | Never — verify API amount format first |
| Copy `UpdateCashBook::toArray()` null-inclusive pattern for receipt DTO | Fast to write | API may reject null values that should be absent; silent data loss | Never — receipt submission semantics differ from update |
| Single test that only submits a valid receipt (happy path only) | Fast CI | Failure exceptions and edge cases untested; users get unhelpful exceptions | MVP only, with explicit TODO to add failure tests |
| Hardcode `$scrada->pos->addReceipts(...)` return type as `true` before knowing API response shape | Simple return type | If API returns a receipt ID or confirmation object, SDK must be rewritten | Never — confirm response shape from API spec first |
| Skip a dedicated `DateTime` primitive and pass ISO string directly | No new class | Bypasses value object validation; type safety lost for timestamped fields | Never |

---

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Scrada Add Receipts endpoint | Assuming the HTTP method is POST because "it's adding data" without checking the spec | Verify HTTP method in the OpenAPI spec — Scrada uses PUT for `UpdateCashBook`; the receipts endpoint may differ |
| Scrada Add Receipts endpoint | Using the CashBook URL pattern (`company/{id}/cashBook/{id}`) as a template and guessing the receipts URL | Confirm exact URL from the API spec; POS endpoints likely follow a different path such as `company/{id}/pos/receipts` |
| Rate limiting | Assuming a single receipt submission fits within rate limits | The token bucket algo is per-credential; batch receipt submissions may exhaust the bucket faster than single reads |
| PHPUnit integration tests | Committing `.env` credentials or leaving the `.env` file without an `.env.example` entry for the new test variables | Add the new env variable (e.g., `CASH_BOOK_ID` equivalent for POS) to `tests/.env.example` before any test is written |
| Saloon `HasJsonBody` + nested objects | Nested value objects in `defaultBody()` that implement `JsonSerializable` will serialize via `jsonSerialize()` automatically — but value objects in this SDK do NOT all implement `JsonSerializable` | Ensure receipt line-item value objects either implement `JsonSerializable` or are manually serialized to scalars in `toArray()` before being placed in the body array |

---

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Submitting receipts one-by-one in a loop without awareness of rate limiting | First N receipts succeed, then `TooManyRequestsException` fires mid-batch | Document that `addReceipts()` is subject to the token bucket; suggest callers back off on 429 | At whatever rate the Scrada API defines (already handled by Saloon `HasRateLimits`, but batch callers may not be aware) |
| Constructing large `toArray()` output with deeply nested structures on every call | Negligible at single-call scale, imperceptible in practice | Non-issue for an SDK making individual API calls — do not over-engineer | N/A for this project's scale |

---

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Including receipt test credentials (API key + password) in committed test fixtures or snapshot files | Credential exposure in version history | All credentials must live in `tests/.env` (gitignored); never hardcode in test files |
| Logging the full request body in debug output (receipt data may contain transaction amounts, customer references) | Exposure of financial transaction data in logs | No debug logging of request bodies in the SDK itself; leave logging to the consumer |
| Using the production environment during integration tests without a guard | Real financial receipts submitted to production Scrada account during test runs | The test suite should default to `Environment::Test`; add an assertion in the test bootstrap that verifies the environment is not production |

---

## "Looks Done But Isn't" Checklist

- [ ] **PosResource wired into Scrada.php:** `$this->pos = new PosResource($this->client)` exists in `Scrada::__construct()` — verify by checking `Scrada.php` directly.
- [ ] **CouldNotAddReceipts exception class created:** File exists at `src/Pos/Add/Failure/CouldNotAddReceipts.php` extending `ValidationException` — verify by running PHPStan.
- [ ] **@throws annotations complete:** `PosResource::addReceipts()` declares `@throws CouldNotAuthenticate`, `@throws CouldNotAddReceipts`, `@throws UnknownException`, `@throws ValidationException` — verify by grep and PHPStan.
- [ ] **Return type confirmed against API spec:** If API returns nothing (204), method returns `true`. If API returns a body, a typed return object must exist — verify from spec, not assumption.
- [ ] **Amount serialization verified:** `toArray()` produces a number format the API accepts (integer cents or decimal string) — verify by inspecting the actual JSON sent in a test run.
- [ ] **Line items serialize as JSON array not object:** `json_encode($body)` shows `"lines": [...]` not `"lines": {"0": {...}}` — verify by dumping the serialized body in a test.
- [ ] **Test env variable added to .env.example:** Any new test credential (e.g., `POS_CASH_BOOK_ID`) is documented in `tests/.env.example` — verify by reading that file.
- [ ] **Laravel Pint passes:** `vendor/bin/pint --test` on new files shows no violations.
- [ ] **PHPStan passes at configured level:** `vendor/bin/phpstan analyse` on new files shows no errors.

---

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Float precision in amounts causing API rejections | LOW | Introduce `Amount` value object with int storage; update `toArray()` serialization; no API contract change needed |
| Null fields causing unexpected API validation errors | LOW | Add `array_filter` or explicit key exclusion to `toArray()`; update tests |
| Wrong HTTP method used (e.g., POST instead of PUT or vice versa) | LOW | Change `protected Method $method` in the request class; one-line fix |
| Wrong URL pattern in `resolveEndpoint()` | LOW | Update endpoint string; re-run integration test |
| Line items serialized as object instead of array | LOW | Add `array_values()` call in `toArray()`; one-line fix, caught by integration test |
| Return type wrong (assumed `true` but API returns body) | MEDIUM | Create response value objects; update resource method signature and all callers |
| Missing `Scrada.php` wiring discovered after release | MEDIUM | Patch release; update changelog; no user code breaks (they get "Undefined property" immediately) |
| Wrong date primitive used (Date instead of DateTime) | MEDIUM | Create `DateTime` value object; update DTO and mapping trait; test suite catches regressions |

---

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Null vs. omit in body serialization | Implementation — DTO design | Unit test `toArray()` output against expected JSON keys |
| Float precision in amounts | Implementation — value object creation | Unit test serialization with known decimal values (e.g., €0.10, €10.10) |
| Line items as object vs. array | Implementation — `toArray()` review | `assert(array_is_list($body['lines']))` in test |
| Missing `@throws` annotations | Implementation — failure class creation | PHPStan run in CI |
| Date format mismatch | Implementation — before choosing date primitive | Integration test that submits a receipt and reads it back |
| Missing `Scrada.php` wiring | Implementation — first task | Smoke test instantiating `Scrada` and accessing `$scrada->pos` |
| Test credential exposure | Implementation — test scaffolding | `tests/.env.example` review; grep for hardcoded keys |
| Wrong HTTP method / URL | Implementation — request class | Integration test hitting actual API endpoint |

---

## Sources

- Direct code analysis: `src/CashBook/Update/UpdateCashBook.php` (null-inclusive toArray pattern)
- Direct code analysis: `src/Core/Type/Primitive/Date.php` (fromTimestamp vs fromString distinction)
- Direct code analysis: `src/Core/Type/Collection.php` (array key preservation behavior)
- Direct code analysis: `src/CashBook/Type/Primitive/Balance.php` (float-based money representation)
- Direct code analysis: `src/Core/Http/ScradaResource.php` (send() failure contract)
- Direct code analysis: `src/Scrada.php` (resource wiring pattern)
- Saloon PHP docs: https://docs.saloon.dev/the-basics/request-body-data/json-body
- PHP manual: `JsonSerializable` interface and `json_encode` behavior with nested objects
- WebSearch: Belgian GKS fiscal receipt requirements (fiskaly.com, fiscal-requirements.com)
- WebSearch: PHP value objects and money representation patterns (dantleech.com, 2024)

---
*Pitfalls research for: PHP SDK — Scrada POS Add Receipts endpoint*
*Researched: 2026-03-25*
