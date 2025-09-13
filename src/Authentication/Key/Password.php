<?php declare(strict_types=1);

namespace Scrada\Authentication\Key;

use SensitiveParameter;
use Webmozart\Assert\Assert;

/** @internal */
final readonly class Password
{
    private function __construct(#[SensitiveParameter] private string $password)
    {
        Assert::length($password, 16, 'Password must be exactly 16 characters long.');
    }

    public static function fromString(#[SensitiveParameter] string $password): self
    {
        return new self($password);
    }

    public function toString(): string
    {
        return $this->password;
    }
}
