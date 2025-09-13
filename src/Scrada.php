<?php declare(strict_types=1);

namespace Scrada;

use Psr\SimpleCache\CacheInterface;
use Saloon\RateLimitPlugin\Stores\PsrStore;
use Scrada\Authentication\Credentials;
use Scrada\CashBook\CashBookResource;
use Scrada\Company\CompanyResource;
use Scrada\Core\Http\ScradaConnector;
use Scrada\Environments\Environment;
use Scrada\Localization\Language;

final readonly class Scrada
{
    public CashBookResource $cashBook;

    public CompanyResource $company;

    private ScradaConnector $client;

    private function __construct(Credentials $credentials)
    {
        $this->client = new ScradaConnector($credentials);
        $this->cashBook = new CashBookResource($this->client);
        $this->company = new CompanyResource($this->client);
    }

    public static function authenticate(Credentials $credentials): Scrada
    {
        return new self($credentials);
    }

    public function useDutch(): self
    {
        $this->client->useLanguage(Language::Dutch);

        return $this;
    }

    public function useEnglish(): self
    {
        $this->client->useLanguage(Language::English);

        return $this;
    }

    public function useFrench(): self
    {
        $this->client->useLanguage(Language::French);

        return $this;
    }

    public function useProduction(): self
    {
        $this->client->useEnvironment(Environment::Production);

        return $this;
    }

    public function useTest(): self
    {
        $this->client->useEnvironment(Environment::Test);

        return $this;
    }

    public function withStore(CacheInterface $store): self
    {
        $this->client->useRateLimitStore(new PsrStore($store));

        return $this;
    }
}
