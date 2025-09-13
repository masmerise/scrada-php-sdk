<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Street implements JsonSerializable, Stringable
{
    private function __construct(private string $street)
    {
        Assert::notEmpty($street, 'Street must not be empty.');
        Assert::maxLength($street, 1000, 'Street must not exceed 1000 characters.');
    }

    public static function fromString(string $street): self
    {
        return new self($street);
    }

    public function jsonSerialize(): string
    {
        return $this->street;
    }

    public function toString(): string
    {
        return $this->street;
    }

    public function __toString(): string
    {
        return $this->street;
    }
}
