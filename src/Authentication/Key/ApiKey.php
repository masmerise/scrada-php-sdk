<?php declare(strict_types=1);

namespace Scrada\Authentication\Key;

use SensitiveParameter;
use Webmozart\Assert\Assert;

/** @internal */
final readonly class ApiKey
{
    private function __construct(#[SensitiveParameter] private string $key)
    {
        Assert::uuid($key, 'The API key must be a valid UUIDv4.');
        Assert::same(substr($key, 14, 1), '4', 'The API key must be a valid UUIDv4.');
    }

    public static function fromString(#[SensitiveParameter] string $key): self
    {
        return new self($key);
    }

    public function toString(): string
    {
        return $this->key;
    }
}
