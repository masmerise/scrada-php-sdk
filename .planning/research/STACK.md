# Stack Research

**Domain:** PHP SDK — POS receipts endpoint addition
**Researched:** 2026-03-25
**Confidence:** HIGH

## Context

This is a subsequent-milestone research task. The stack is already established at v0.2.0. No new dependencies are needed. The goal is to confirm the exact versions installed, verify the patterns to follow, and flag the one decision point the POS Receipts feature introduces: how to structure a collection of receipt line items within the existing value-object system.

## Installed Stack (Locked)

These are not choices — they are the existing constraints. All versions confirmed via `composer show`.

### Core Technologies

| Technology | Version | Purpose | Why It's Here |
|------------|---------|---------|----------------|
| PHP | ~8.5 | Runtime | Project constraint; enables `readonly` constructors, named arguments, intersection types, `never` return type |
| saloonphp/saloon | v3.15.0 | HTTP connector, request/response abstraction | Standard SDK framework; provides `Connector`, `Request`, `Method`, `HasJsonBody`, retry, middleware |
| saloonphp/rate-limit-plugin | v2.4.0 | Token-bucket rate limiting against Scrada's 429 responses | Companion plugin to Saloon; integrates via `HasRateLimits` trait on the connector |
| psr/simple-cache | ^3.0 | PSR-16 cache contract for rate-limit store | Allows callers to inject any PSR-16 cache (Redis, APCu, etc.) without hard coupling |
| webmozart/assert | ^1.12 | Runtime validation inside value objects | Used in all primitive value objects (e.g. `Balance`, `Iban`) to throw on bad input with clear messages |

### Development Tools

| Tool | Version | Purpose | Notes |
|------|---------|---------|-------|
| phpunit/phpunit | 12.5.14 | Integration test runner | Tests hit the real Scrada API; credentials via dotenv |
| phpstan/phpstan | 2.1.43 | Static analysis | Run with `composer stan`; catches type errors that PHP misses at runtime |
| laravel/pint | v1.29.0 | Code style enforcement | Opinionated formatter based on PHP-CS-Fixer; run with `composer format` |
| vlucas/phpdotenv | ^5.6.3 | `.env` loading for test credentials | Dev-only; `$_ENV['COMPANY_ID']`, `$_ENV['CASH_BOOK_ID']` etc. |

## Patterns to Follow for POS Receipts

The following patterns are already established and must be matched exactly.

### 1. Directory layout

```
src/Pos/
  Add/
    Failure/CouldNotAddReceipts.php
    Request/AddReceiptsRequest.php
  Type/
    Primitive/          ← one file per primitive value object
    Mapping/MapsPos*.php  ← trait for response-to-VO mapping (if response has a body)
  PosResource.php
```

The `Add/` action directory mirrors `CashBook/Update/` and `CashBook/All/`.

### 2. POST request with JSON body

Use the already-established `HasBody` + `HasJsonBody` pattern from `UpdateCashBookRequest`:

```php
final class AddReceiptsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CompanyId $companyId,
        private readonly CashBookId $cashBookId,
        private readonly AddReceipts $data,
    ) {}

    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function resolveEndpoint(): string
    {
        return "company/{$companyId->toString()}/cashBook/{$cashBookId->toString()}/pos";
        // Exact path TBD from API spec — placeholder above
    }
}
```

Imports: `Saloon\Contracts\Body\HasBody`, `Saloon\Traits\Body\HasJsonBody`, `Saloon\Enums\Method`, `Saloon\Http\Request`.

### 3. Data DTO (parameter object)

Follows `UpdateCashBook` pattern: private constructor, static `parameters()` factory, `toArray()` serializer.

```php
final readonly class AddReceipts
{
    private function __construct(
        public readonly ReceiptDate $date,
        public readonly ReceiptLines $lines,
        // ...other typed fields
    ) {}

    public static function parameters(
        ReceiptDate $date,
        ReceiptLines $lines,
        // ...
    ): self {
        return new self(...);
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date->toString(),
            'lines' => array_map(fn($l) => $l->toArray(), $this->lines->toArray()),
            // ...
        ];
    }
}
```

### 4. Collection of line items

The existing `Core\Type\Collection` class is the pattern for typed collections (see `CashBooks`). Receipt lines will be a `Collection` subclass if the API returns a list, or a plain typed array in the DTO if it is only an input.

```php
// src/Core/Type/Collection.php provides the base
// CashBooks extends it — follow the same approach for ReceiptLines
```

### 5. Failure exception

```php
final class CouldNotAddReceipts extends ValidationException {}
```

Mirrors `CouldNotUpdateCashBook`. `ValidationException` is abstract and wraps a `ScradaError` automatically.

### 6. Resource method

```php
// In PosResource extends ScradaResource
public function add(
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
```

If the endpoint returns a body (e.g. a receipt ID), add a mapping trait and return a typed value object instead of `true`.

### 7. Registration in Scrada.php

```php
public PosResource $pos;

// In __construct:
$this->pos = new PosResource($this->client);
```

### 8. Tests

Integration-style, PHPUnit 12, `#[Test]` + `#[Group('pos')]` attributes. Credentials from `$_ENV`. Trait mixed into `ScradaTest`. No mocking — hit real API.

## Decision Point: POS receipt line items shape

This is the one open question the existing stack does not answer. The Scrada API spec (JS-rendered, must be provided manually) will determine:

- Are receipt lines a flat array of objects, or are they nested?
- Does the endpoint return a response body (receipt ID, status) or HTTP 200/204 with no body?

If there is a response body with a collection of items, add a mapping trait (e.g. `MapsPosReceipts`) following `MapsCashBooks`. If the response is empty, return `true` from the resource method.

**No new dependencies are needed for either case.**

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| Adding a new HTTP client (Guzzle directly, Symfony HttpClient) | The connector is already Saloon-managed; bypassing it loses retry, rate-limit, auth middleware | `Saloon\Http\Request` + `HasJsonBody` |
| `illuminate/collections` (Laravel Collection) | Not a project dependency; Saloon suggests it but the codebase uses its own `Core\Type\Collection` | `Scrada\Core\Type\Collection` |
| Spatie/data or similar DTO libraries | Adds a dependency for no gain; the existing `parameters()` + `toArray()` pattern is minimal and consistent | Plain readonly classes with static factory |
| PHP-CS-Fixer directly | Laravel Pint already wraps it; double-configuring will conflict | `composer format` (Pint) |
| Mocking/faking the HTTP layer in tests | Existing tests are integration tests against the real API; introducing WireMock or Saloon's `MockClient` mid-milestone breaks consistency | Real API + dotenv credentials |

## Version Compatibility

| Package | Compatible With | Notes |
|---------|-----------------|-------|
| saloonphp/saloon v3.15.0 | PHP ^8.2 | Project uses ~8.5, well within range |
| saloonphp/rate-limit-plugin v2.4.0 | saloonphp/saloon ^3.0 | Locked to Saloon v3 major |
| phpunit/phpunit 12.5.14 | PHP ^8.2 | Attribute-based (`#[Test]`, `#[Group]`) — no `@` docblock annotations |
| phpstan/phpstan 2.1.43 | PHP ^8.2 | Run at max practical level via `phpstan.neon` in repo |
| laravel/pint v1.29.0 | PHP ^8.1 | Covers 8.5 |

## Sources

- `composer show saloonphp/saloon` — version v3.15.0, released 2026-03-02 (HIGH confidence, installed package)
- `composer show saloonphp/rate-limit-plugin` — version v2.4.0, released 2025-12-18 (HIGH confidence, installed package)
- `composer show phpunit/phpunit` — version 12.5.14 (HIGH confidence, installed package)
- `composer show phpstan/phpstan` — version 2.1.43 (HIGH confidence, installed package)
- `composer show laravel/pint` — version v1.29.0 (HIGH confidence, installed package)
- https://docs.saloon.dev/the-basics/request-body-data/json-body — `HasBody` interface + `HasJsonBody` trait pattern confirmed current in v3 docs (HIGH confidence)
- Codebase inspection: `src/CashBook/Update/Request/UpdateCashBookRequest.php`, `src/Core/Http/ScradaResource.php`, `src/Scrada.php`, `src/CashBook/CashBookResource.php` — patterns extracted directly from source (HIGH confidence)

---
*Stack research for: Scrada PHP SDK — POS Add Receipts endpoint*
*Researched: 2026-03-25*
