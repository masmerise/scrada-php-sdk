<!-- GSD:project-start source:PROJECT.md -->
## Project

**Scrada PHP SDK**

An unofficial PHP SDK for accessing Scrada's API, a Belgian digital bookkeeping platform for POS software ("kassasystemen"). The SDK covers Company, CashBook, and POS resources using Saloon PHP, with strong typing via value objects and a clear resource/request/failure pattern.

**Core Value:** Provide a fully-typed, convention-consistent PHP interface for accessing Scrada's API — matching established SDK patterns exactly.

### Constraints

- **Tech stack**: PHP ~8.5, Saloon PHP v4, must follow existing patterns
- **Convention**: All new code must match established SDK conventions (value objects, mapping traits, resource classes, typed failures)
- **API spec**: Endpoint details need to be provided manually (can't scrape JS-rendered docs)
<!-- GSD:project-end -->

<!-- GSD:stack-start source:research/STACK.md -->
## Technology Stack

## Context
## Installed Stack (Locked)
### Core Technologies
| Technology | Version | Purpose | Why It's Here |
|------------|---------|---------|----------------|
| PHP | ~8.5    | Runtime | Project constraint; enables `readonly` constructors, named arguments, intersection types, `never` return type |
| saloonphp/saloon | v4.0.0  | HTTP connector, request/response abstraction | Standard SDK framework; provides `Connector`, `Request`, `Method`, `HasJsonBody`, retry, middleware |
| saloonphp/rate-limit-plugin | v2.0    | Token-bucket rate limiting against Scrada's 429 responses | Companion plugin to Saloon; integrates via `HasRateLimits` trait on the connector |
| psr/simple-cache | ^3.0    | PSR-16 cache contract for rate-limit store | Allows callers to inject any PSR-16 cache (Redis, APCu, etc.) without hard coupling |
| webmozart/assert | ^2.0    | Runtime validation inside value objects | Used in all primitive value objects (e.g. `Balance`, `Iban`) to throw on bad input with clear messages |
### Development Tools
| Tool | Version | Purpose | Notes |
|------|---------|---------|-------|
| phpunit/phpunit | 13.0.5  | Integration test runner | Tests hit the real Scrada API; credentials via dotenv |
| phpstan/phpstan | 2.1.44  | Static analysis | Run with `composer stan`; catches type errors that PHP misses at runtime |
| laravel/pint | v1.29.0 | Code style enforcement | Opinionated formatter based on PHP-CS-Fixer; run with `composer format` |
| vlucas/phpdotenv | ^5.6.3  | `.env` loading for test credentials | Dev-only; `$_ENV['COMPANY_ID']`, `$_ENV['CASH_BOOK_ID']` etc. |
## Patterns to Follow
### 1. Directory layout
### 2. POST request with JSON body
### 3. Data DTO (parameter object)
### 4. Collection of line items
### 5. Failure exception
### 6. Resource method
### 7. Registration in Scrada.php
### 8. Tests
## What NOT to Use
| Avoid | Why | Use Instead |
|-------|-----|-------------|
| Adding a new HTTP client (Guzzle directly, Symfony HttpClient) | The connector is already Saloon-managed; bypassing it loses retry, rate-limit, auth middleware | `Saloon\Http\Request` + `HasJsonBody` |
| `illuminate/collections` (Laravel Collection) | Not a project dependency; Saloon suggests it but the codebase uses its own `Core\Type\Collection` | `Scrada\Core\Type\Collection` |
| Spatie/data or similar DTO libraries | Adds a dependency for no gain; the existing `parameters()` + `toArray()` pattern is minimal and consistent | Plain readonly classes with static factory |
| PHP-CS-Fixer directly | Laravel Pint already wraps it; double-configuring will conflict | `composer format` (Pint) |
| Mocking/faking the HTTP layer in tests | Existing tests are integration tests against the real API; introducing WireMock or Saloon's `MockClient` mid-milestone breaks consistency | Real API + dotenv credentials |
## Version Compatibility
| Package                            | Compatible With | Notes |
|------------------------------------|-----------------|-------|
| saloonphp/saloon v4.0.0            | PHP ^8.2 | Project uses ~8.5, well within range |
| saloonphp/rate-limit-plugin v2.5.1 | saloonphp/saloon ^4.0 | Compatible with Saloon v4 |
| phpunit/phpunit 13.0.5             | PHP ^8.2 | Attribute-based (`#[Test]`, `#[Group]`) — no `@` docblock annotations |
| phpstan/phpstan 2.1.44             | PHP ^8.2 | Run at max practical level via `phpstan.neon` in repo |
| laravel/pint v1.29.0               | PHP ^8.1 | Covers 8.5 |
## Sources
- `composer show saloonphp/saloon` — version v4.0.0 (HIGH confidence, installed package)
- `composer show saloonphp/rate-limit-plugin` — version v2.0 (HIGH confidence, installed package)
- `composer show webmozart/assert` — version 2.0 (HIGH confidence, installed package)
- `composer show phpunit/phpunit` — version 13.0.5 (HIGH confidence, installed package)
- `composer show phpstan/phpstan` — version 2.1.44 (HIGH confidence, installed package)
- `composer show laravel/pint` — version v1.29.0 (HIGH confidence, installed package)
<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->
## Conventions

Conventions not yet established. Will populate as patterns emerge during development.
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->
## Architecture

Architecture not yet mapped. Follow existing patterns found in the codebase.
<!-- GSD:architecture-end -->

<!-- GSD:workflow-start source:GSD defaults -->
## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:
- `/gsd:quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd:debug` for investigation and bug fixing
- `/gsd:execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->



<!-- GSD:profile-start -->
## Developer Profile

> Profile not yet configured. Run `/gsd:profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
