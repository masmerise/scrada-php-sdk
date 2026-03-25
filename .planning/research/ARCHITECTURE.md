# Architecture Research

**Domain:** PHP SDK — POS Add Receipts endpoint (Scrada API)
**Researched:** 2026-03-25
**Confidence:** HIGH (based on direct codebase analysis of v0.2.0)

## Standard Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                          Public API Layer                            │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  Scrada  (entry point — static factory + fluent config)      │   │
│  │  $scrada->pos->addReceipts(companyId, cashBookId, receipt)   │   │
│  └──────────────────────────┬───────────────────────────────────┘   │
└─────────────────────────────┼───────────────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────────────┐
│                        Resource Layer                                │
│                                                                      │
│  ┌──────────────┐  ┌───────────────┐  ┌──────────────────────────┐  │
│  │ CashBook     │  │ Company       │  │ Pos                      │  │
│  │ Resource     │  │ Resource      │  │ Resource  (new)          │  │
│  └──────────────┘  └───────────────┘  └──────────────────────────┘  │
│  All extend ScradaResource — inherit send(), MapsScradaErrors trait  │
└─────────────────────────────┬───────────────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────────────┐
│                       HTTP / Connector Layer                         │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  ScradaConnector  (Saloon Connector)                         │   │
│  │  · Dual-header auth (X-API-KEY + X-PASSWORD)                 │   │
│  │  · Base URL switching (production/test)                      │   │
│  │  · Language header injection                                 │   │
│  │  · Rate limiting (token bucket via HasRateLimits trait)      │   │
│  │  · Exponential backoff retry (tries=2, retryInterval=1000ms) │   │
│  └──────────────────────────────────────────────────────────────┘   │
└─────────────────────────────┬───────────────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────────────┐
│                         Scrada REST API                              │
│  https://api.scrada.be/v1    (production)                            │
│  https://apitest.scrada.be/v1  (test)                                │
└─────────────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Communicates With |
|-----------|----------------|-------------------|
| `Scrada` | Public entry point. Static factory, fluent language/env config, exposes resource properties | `ScradaConnector`, all Resource classes |
| `ScradaConnector` | Saloon Connector. Auth headers, base URL, rate limiting, retry, per-credential rate limit scope | Scrada API via HTTP |
| `ScradaResource` (abstract) | Base for all resources. Wraps `send()` with error mapping, authentication failure handling, JSON decode | `ScradaConnector`, `MapsScradaErrors` trait |
| `PosResource` (new) | Exposes `addReceipts()`. Delegates to Request, maps response (or confirms void), maps failures | `ScradaConnector` via `send()`, mapping trait |
| `AddReceiptsRequest` (new) | Saloon Request. Encodes endpoint URL and JSON body from typed DTO. Implements `HasBody` | `ScradaConnector` |
| `AddReceipts` DTO (new) | Input parameter object. Named constructor `parameters(...)`, `toArray()` serialiser, all fields typed | Used by `PosResource`, passed to Request |
| Nested DTOs (new) | `ReceiptLine`, `Payment`, `TaxLine` — sub-objects of `AddReceipts`. Same pattern: private constructor, `parameters()`, `toArray()` | Composed inside `AddReceipts` |
| Primitive value objects (new) | Scalar wrappers with validation: `ReceiptId`, `Amount`, `VatRate`, `Quantity`, `Description`, etc. | Used by DTOs |
| `CouldNotAddReceipts` (new) | Typed failure exception extending `ValidationException`. Carries `ScradaError`. | Thrown by `PosResource` |
| Mapping trait (new) | `MapsReceipts` (if response contains receipt data) — or omitted entirely for a write-only endpoint | Used by `PosResource` |
| `ScradaError` | Structured API error payload (code, type, message, parameters, inner errors) | `ValidationException` subclasses |
| `Collection<T>` | Typed immutable iterable base for multi-item responses | Extended by domain collection types |

## Recommended Project Structure

```
src/
├── Pos/                              # New domain module
│   ├── PosResource.php               # Public resource, addReceipts() method
│   ├── AddReceipts/                  # Action-scoped subdirectory
│   │   ├── Failure/
│   │   │   └── CouldNotAddReceipts.php   # extends ValidationException
│   │   └── Request/
│   │       └── AddReceiptsRequest.php    # Saloon Request, HasBody + HasJsonBody
│   └── Type/                         # Domain value objects
│       ├── AddReceipts.php           # Root input DTO
│       ├── ReceiptLine.php           # Line item sub-DTO
│       ├── Payment.php               # Payment sub-DTO
│       ├── TaxLine.php               # Tax sub-DTO (if separate from line)
│       └── Primitive/
│           ├── ReceiptId.php         # UUIDv4 wrapper (if receipts have IDs)
│           ├── Amount.php            # Monetary amount (float-based, >= 0)
│           ├── Quantity.php          # Quantity (int or float)
│           ├── VatRate.php           # VAT percentage primitive
│           ├── Description.php       # Non-empty string
│           └── ...                   # Other typed scalars as needed
├── Scrada.php                        # Add: public PosResource $pos
```

### Structure Rationale

- **`Pos/` module root:** Each Scrada API domain (Company, CashBook, POS) is an isolated top-level module. POS receipts maps naturally to its own module.
- **`AddReceipts/` action subdirectory:** Each action has its own subdirectory (`Get/`, `Update/`, `All/`, now `AddReceipts/`) with `Failure/` and `Request/` sub-namespaces. This is the established convention.
- **`Type/Primitive/`:** Domain-specific primitive value objects live here. Shared primitives (`Date`) live in `Core/Type/Primitive/`. New receipt-domain scalars (amounts, VAT rates) belong under `Pos/Type/Primitive/`.
- **`Type/` DTOs:** Input parameter objects (`AddReceipts`, `ReceiptLine`, `Payment`) sit at the `Type/` level, not inside the action directory, because they are domain types reusable across actions.

## Architectural Patterns

### Pattern 1: Named Constructor + Private Constructor (Immutable DTO)

**What:** All value objects and input DTOs have a private constructor and one or more static named constructors (`parameters()`, `fromString()`, `fromFloat()`, etc). They are `final readonly`.

**When to use:** Every new DTO and primitive. Without exception — this is the enforced convention.

**Trade-offs:** Verbose, but provides explicit construction semantics, prevents partial construction, enables PHPStan exhaustiveness.

**Example:**
```php
final readonly class AddReceipts
{
    private function __construct(
        public ReceiptId $id,
        public Date $date,
        /** @var ReceiptLine[] */
        public array $lines,
        /** @var Payment[] */
        public array $payments,
    ) {}

    public static function parameters(
        ReceiptId $id,
        Date $date,
        array $lines,
        array $payments,
    ): self {
        return new self(
            id: $id,
            date: $date,
            lines: $lines,
            payments: $payments,
        );
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id->toString(),
            'date'     => $this->date->toString(),
            'lines'    => array_map(fn (ReceiptLine $l) => $l->toArray(), $this->lines),
            'payments' => array_map(fn (Payment $p) => $p->toArray(), $this->payments),
        ];
    }
}
```

### Pattern 2: Resource + Action Request + Failure Exception

**What:** Resources extend `ScradaResource` and call `$this->send(request: ..., onFailure: ...)`. The request encodes the HTTP method, endpoint, and body. The failure class is passed by class-string so `send()` can instantiate it with the `ScradaError` payload.

**When to use:** Every new endpoint. This is the only way new actions are wired into the SDK.

**Trade-offs:** Predictable, testable, and consistent. Slightly ceremonial for simple endpoints.

**Example:**
```php
final readonly class PosResource extends ScradaResource
{
    public function addReceipts(
        CompanyId $companyId,
        CashBookId $cashBookId,
        AddReceipts $data,
    ): true {
        $this->send(
            request: new AddReceiptsRequest($companyId, $cashBookId, $data),
            onFailure: CouldNotAddReceipts::class,
        );

        return true;
    }
}
```

### Pattern 3: Mapping Traits for Response Hydration

**What:** When an action returns domain objects (GET/list responses), the Resource class uses a `Maps*` trait (`MapsCashBooks`, `MapsCompanies`). The trait defines a private `to*()` method that accepts a typed array shape (PHPDoc) and returns a hydrated value object by calling `::parameters()`.

**When to use:** Only when the response contains a body to deserialize. A pure write endpoint (POST with no response body, or a `true` return) does not need a mapping trait.

**Trade-offs:** Keeps hydration logic out of the Resource, making the trait independently testable. If `addReceipts` returns a receipt ID or confirmation object, add `MapsReceipts` trait. If it returns HTTP 200 with empty body or just `true`, omit the trait.

## Data Flow

### Request Flow (addReceipts — write endpoint)

```
Caller code
    ↓
Scrada::authenticate(Credentials::present($key, $password))
    ↓
$scrada->pos->addReceipts($companyId, $cashBookId, AddReceipts::parameters(...))
    ↓
PosResource::addReceipts()
    ↓  calls
ScradaResource::send(AddReceiptsRequest, CouldNotAddReceipts::class)
    ↓  delegates to
ScradaConnector::send()   [applies auth headers, language header, rate limiting, retry]
    ↓  HTTP POST
Scrada API  POST /company/{companyId}/cashBook/{cashBookId}/pos/receipts
    ↓
HTTP Response
    ↓  success path
return true

    ↓  failure path
ScradaResource::send() detects non-2xx
    ↓
if 401 → throw CouldNotAuthenticate
if other → throw CouldNotAddReceipts(ScradaError)
```

### Type Construction Flow (input side)

```
Caller builds primitives
  Amount::fromFloat(12.50)
  VatRate::fromInt(21)
  Description::fromString('Coffee')
    ↓
Caller builds line items
  ReceiptLine::parameters(description, quantity, unitPrice, vatRate, ...)
    ↓
Caller builds payment records
  Payment::parameters(method, amount, ...)
    ↓
Caller builds root DTO
  AddReceipts::parameters(id, date, lines, payments, ...)
    ↓
Passed to PosResource::addReceipts()
    ↓
AddReceiptsRequest::defaultBody() calls $data->toArray()
  → recursively serialises nested DTOs via their toArray() methods
    ↓
JSON-encoded by Saloon's HasJsonBody trait
    ↓
HTTP POST body
```

### Key Data Flows

1. **Nested DTO serialisation:** `AddReceipts::toArray()` calls `toArray()` on each `ReceiptLine` and `Payment`. Each of those calls `toString()` / `toFloat()` / `->value` on their primitives. This is the consistent pattern — no JSON serialisation logic leaks into the Request class.
2. **Error propagation:** API errors flow from HTTP response → `ScradaResource::send()` → `toScradaError()` (via `MapsScradaErrors` trait) → typed exception carrying `ScradaError`. Callers catch specific `CouldNotAddReceipts` or the `CouldNotAuthenticate`/`UnknownException` base cases.

## Component Boundaries

| Boundary | Rule |
|----------|------|
| `Scrada` ↔ Resources | `Scrada` owns resource instances as `public readonly` properties. Resources are instantiated in the constructor, never lazily. |
| Resource ↔ Connector | Resources never build HTTP requests directly. They always go through `ScradaResource::send()`. |
| Request ↔ DTO | Requests call `$data->toArray()` in `defaultBody()`. Requests never construct value objects — that is the caller's responsibility. |
| Core ↔ Domain | Domain modules (`Pos/`, `CashBook/`, `Company/`) may depend on `Core/`. `Core/` must never depend on any domain module. Shared primitives (`Date`, `Collection`) live in `Core/`. |
| `@internal` boundary | `ScradaConnector`, `ScradaResource`, all Request classes, and all mapping traits are marked `@internal`. Callers interact only via the `Scrada` facade and public domain types. |

## Build Order (Dependency Graph)

The following order respects class-level dependencies and mirrors how both existing resources were built:

```
1. Primitive value objects
   Pos/Type/Primitive/{ReceiptId, Amount, Quantity, VatRate, Description, ...}
   (no dependencies within Pos module — may use Core/Type/Primitive/Date)

2. Sub-DTOs
   Pos/Type/ReceiptLine     (depends on primitives)
   Pos/Type/Payment         (depends on primitives)
   Pos/Type/TaxLine         (depends on primitives, if separate)

3. Root input DTO
   Pos/Type/AddReceipts     (depends on sub-DTOs and primitives)

4. Request class
   Pos/AddReceipts/Request/AddReceiptsRequest
   (depends on AddReceipts DTO, CompanyId, CashBookId from existing modules)

5. Failure exception
   Pos/AddReceipts/Failure/CouldNotAddReceipts
   (depends only on ValidationException from Core)

6. Resource
   Pos/PosResource
   (depends on request, failure, AddReceipts DTO, ScradaResource)

7. Entry point wiring
   Scrada.php — add `public PosResource $pos` and instantiate in constructor

8. Tests
   tests/Pos/PosTests.php trait + wired into ScradaTest
```

## Anti-Patterns

### Anti-Pattern 1: Constructing Objects Inside Requests

**What people do:** Build value objects or do data transformation inside `AddReceiptsRequest::defaultBody()`.

**Why it's wrong:** Requests are internal transport objects. Transformation logic in a request is untestable without HTTP scaffolding and breaks the boundary where Requests only serialise, they don't construct.

**Do this instead:** All construction happens in the caller. `defaultBody()` calls `$this->data->toArray()` only.

### Anti-Pattern 2: Flat Array for Nested Receipt Data

**What people do:** Accept `array $lines` typed as plain PHP array instead of `ReceiptLine[]` objects.

**Why it's wrong:** Loses the strong typing the entire SDK is built on. PHPStan can't verify field completeness. Callers get no IDE assistance.

**Do this instead:** Model every sub-component (line items, payments, tax lines) as a separate `final readonly` DTO with its own primitives. Compose them into the root `AddReceipts` DTO.

### Anti-Pattern 3: Skipping the Failure Class

**What people do:** Use a generic exception instead of defining `CouldNotAddReceipts`.

**Why it's wrong:** Callers cannot catch operation-specific failures. The `onFailure` parameter in `send()` exists precisely to provide typed, catchable exceptions per operation.

**Do this instead:** Always define a minimal `CouldNotAddReceipts extends ValidationException {}` class — even if it contains only one line.

### Anti-Pattern 4: Modifying Existing Files Beyond Scrada.php

**What people do:** Add POS-related concerns to `ScradaResource`, `ScradaConnector`, or existing resource classes.

**Why it's wrong:** PROJECT.md explicitly requires "extend, don't change". Existing tests and consumers rely on current contracts.

**Do this instead:** All new code lives under `Pos/`. The only modification to an existing file is adding `public PosResource $pos` to `Scrada.php` and constructing it in the constructor — identical to how `CashBookResource` and `CompanyResource` were added.

## Scaling Considerations

This is an SDK library, not a deployed service. Scaling concerns are call-site concerns, not SDK architecture concerns. The relevant SDK-level concern is:

| Concern | Current approach | Notes |
|---------|-----------------|-------|
| Rate limiting | Token bucket, per-credential scope | Already handled by `ScradaConnector`. No changes needed for POS endpoint. |
| Retry | 2 tries, exponential backoff, 1 second base | Already handled. POS POST requests must be idempotent (or the API must handle duplicate receipt IDs) — this is an API-level constraint, not SDK-level. |
| Memory | Collections are immutable, no lazy loading | POS `addReceipts` is likely a write-only endpoint returning `true` — no collection allocation needed. |

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| Scrada API (production) | HTTPS REST, JSON, dual-header auth | `https://api.scrada.be/v1` |
| Scrada API (test) | Same as production, different base URL | `https://apitest.scrada.be/v1` |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| `Pos/` module ↔ `Company/` module | Direct class reference (`CompanyId` is used in POS endpoint URL) | One-way dependency only — Company does not know about Pos |
| `Pos/` module ↔ `CashBook/` module | Direct class reference (`CashBookId` is used in POS endpoint URL) | One-way dependency only |
| `Pos/` module ↔ `Core/` module | Direct class reference (`Date`, `Collection`, `ScradaResource`, `ValidationException`) | Standard — all domain modules depend on Core |

## Sources

- Direct analysis of `scrada-php-sdk` v0.2.0 source (`src/`, `tests/`)
- `src/CashBook/CashBookResource.php` — canonical Resource pattern
- `src/CashBook/Update/UpdateCashBook.php` — canonical input DTO pattern
- `src/CashBook/Type/Mapping/MapsCashBooks.php` — canonical mapping trait pattern
- `src/Core/Http/ScradaResource.php` — base resource with `send()` and error mapping
- `src/Core/Http/ScradaConnector.php` — connector with auth, rate limiting, retry
- `src/Core/Type/Collection.php` — typed immutable collection base
- `src/Scrada.php` — entry point and resource wiring pattern

---
*Architecture research for: Scrada PHP SDK — POS Add Receipts endpoint*
*Researched: 2026-03-25*
