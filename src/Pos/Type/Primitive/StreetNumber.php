<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class StreetNumber implements JsonSerializable, Stringable
{
    private function __construct(private string $streetNumber)
    {
        Assert::notEmpty($streetNumber, 'Street number must not be empty.');
    }

    public static function fromString(string $streetNumber): self
    {
        return new self($streetNumber);
    }

    public function jsonSerialize(): string
    {
        return $this->streetNumber;
    }

    public function toString(): string
    {
        return $this->streetNumber;
    }

    public function __toString(): string
    {
        return $this->streetNumber;
    }
}
