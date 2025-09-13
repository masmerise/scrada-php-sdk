<?php declare(strict_types=1);

namespace Scrada\Core\Http;

use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Stores\PsrStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Scrada\Authentication\Credentials;
use Scrada\Environments\Environment;
use Scrada\Localization\Language;
use Scrada\RateLimiting\TokenBucketAlgo;

/** @internal */
final class ScradaConnector extends Connector
{
    use HasRateLimits;

    public ?int $tries = 2;

    public ?int $retryInterval = 1000;

    public ?bool $useExponentialBackoff = true;

    private Environment $environment = Environment::Production;

    private Language $language = Language::English;

    private RateLimitStore $store;

    public function __construct(private readonly Credentials $credentials)
    {
        $this->store = new MemoryStore();
    }

    public function resolveBaseUrl(): string
    {
        return $this->environment->url();
    }

    public function useEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }

    public function useLanguage(Language $language): void
    {
        $this->language = $language;
    }

    public function useRateLimitStore(PsrStore $store): void
    {
        $this->store = $store;
    }

    protected function defaultAuth(): MultiAuthenticator
    {
        return new MultiAuthenticator(
            new HeaderAuthenticator($this->credentials->key->toString(), 'X-API-KEY'),
            new HeaderAuthenticator($this->credentials->password->toString(), 'X-PASSWORD'),
        );
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 10,
        ];
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Language' => $this->language->value,
        ];
    }

    protected function getLimiterPrefix(): string
    {
        return "scrada:{$this->credentials->toHash()}";
    }

    protected function handleTooManyAttempts(Response $response, Limit $limit): void
    {
        if ($response->status() !== 429) {
            return;
        }

        $nextTokenReplenishmentAt = (int) $response->header('X-Ratelimit-Reset');

        $limit->exceeded(
            releaseInSeconds: $nextTokenReplenishmentAt - time()
        );
    }

    protected function resolveLimits(): array
    {
        return TokenBucketAlgo::define();
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return $this->store;
    }
}
