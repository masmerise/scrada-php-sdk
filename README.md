<p align="center">
<img src="https://www.scrada.be/wp-content/uploads/2023/10/ScradaLogoWebsite.png" alt="Scrada PHP SDK" height="50">
<img src="https://www.php.net/images/logos/php-logo.svg" alt="PHP" height="40">
</p>

<p align="center">
<a href="https://github.com/masmerise/scrada-php-sdk/actions"><img src="https://github.com/masmerise/scrada-php-sdk/actions/workflows/test.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/masmerise/scrada-php-sdk"><img src="https://img.shields.io/packagist/dt/masmerise/scrada-php-sdk" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/masmerise/scrada-php-sdk"><img src="https://img.shields.io/packagist/v/masmerise/scrada-php-sdk" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/masmerise/scrada-php-sdk"><img src="https://img.shields.io/packagist/l/masmerise/scrada-php-sdk" alt="License"></a>
</p>

## Scrada PHP SDK

This SDK provides convenient, fully-typed access to [Scrada's API](https://www.scrada.be/api-documentation/).

## Installation

You can install the package via [composer](https://getcomposer.org):

```bash
composer require masmerise/scrada-php-sdk
```

## Getting started

You can always refer to the [documentation](https://www.scrada.be/api-documentation/) to examine the various resources that are available.

### Setup

```php
use Scrada\Authentication\Credentials;
use Scrada\Scrada;

$credentials = Credentials::present(
    key: '4844a45c-33d1-4937-83f4-366d36449eaf', 
    password: 'SajA1NOEphxVMwTTFzrDswj3AQkEGCCJ',
); 

$scrada = Scrada::authenticate($credentials);
```

### Data retrieval

```php
use Scrada\Company\Type\Primitive\CompanyId;

$company = CompanyId::fromString('4ccc0005-0bdd-430b-8f45-264c3f1a2a02'); 
$company = $scrada->company->get($company);
```

### Resource update

```php
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Type\Primitive\CodaGenerationPeriod;
use Scrada\CashBook\Type\Primitive\Name;
use Scrada\CashBook\Update\UpdateCashBook;
use Scrada\Company\Type\Primitive\CompanyId;

$companyId = CompanyId::fromString('4ccc0005-0bdd-430b-8f45-264c3f1a2a02');

$cashBookId = CashBookId::fromString('d84835b0-7125-4f8f-b10a-957a0cc73089');

$data = UpdateCashBook::parameters(
    name: Name::fromString('Acme Inc.'),
    codaGenerationPeriodType: CodaGenerationPeriod::EveryDay,
);

$scrada->cashBook->update($companyId, $cashBookId, $data);
```

## Failures (exception handling)

The SDK uses exceptions as its medium to communicate failures.

```
ScradaException
├── UnknownException (Connection Errors)
└── ScradaApiException (Request Errors)
    ├── CouldNotGetCompany
    ├── CouldNotUpdateCompany
    ├── CouldNotGetAllCashBooks
    ├── CouldNotUpdateCashBook
    └── ...
```

```php
use Scrada\Company\Get\Failure\CouldNotGetCompany;

try {
    $company = $scrada->company->get($id);
} catch (CouldNotGetCompany $ex) {
    $logger->log($ex->getMessage());
}
```

### API failures

API interaction failures, i.e. exceptions of type `ScradaApiException`, 
always expose a special `ScradaError` object that contains the API's reasons for rejection:

```php
use Scrada\Company\Get\Failure\CouldNotGetCompany;

try {
    $company = $scrada->company->get($id);
} catch (CouldNotGetCompany $ex) {
    $scradaError = $ex->error;

    $logger->log($scradaError->defaultFormat); // Localized error text
}
```

## Rate limiting

The SDK is equipped with out-of-the-box capabilities to deal with Scrada's request constraints.
In order to make use of this, you will have to provide a [`PSR-16`](https://www.php-fig.org/psr/psr-16/) compatible cache store so the SDK
can keep track of its current state.

Chances are that you are going to use this SDK in conjunction with a popular web framework,
and most of them already have a PSR-16 compatible implementation of a cache store available:

```php
$store = Container::getInstance()->get('cache');

$scrada = Scrada::authenticate(
    Credentials::present(...)
)->withStore($store);
```

## Localization 

You can use one of the available methods to change the API's language:

### Dutch

```php
$scrada->useDutch();
```

### English (default)

```php
$scrada->useEnglish();
```

### French

```php
$scrada->useFrench();
```

## Environment

You can use one of the available methods to change the API's environment:

### Production (default)

```php
$scrada->useProduction();
```

### Test

```php
$scrada->useTest();
```

## Retries

The SDK is instrumented with automatic retries with exponential backoff. A request will be retried as long
as the request is deemed retriable and the number of retry attempts has not grown larger than the default
retry limit (2).

A request is deemed retriable when any of the following HTTP status codes is returned:

- [408](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408) (Timeout)
- [429](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429) (Too Many Requests)
- [5XX](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500) (Internal Server Errors)

## Timeouts

The SDK defaults to a 10 second timeout. 

## Progress

While the SDK is battle-tested and production-ready, only a handful of API interactions have been implemented thus far.
Please bear in mind this is an unofficial SDK, so we have to prioritize available resources at this time.

However, always feel free to submit a feature or pull request!

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email support@masmerise.be instead of using the issue tracker.

## Credits

- [Muhammed Sari](https://github.com/mabdullahsari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
