<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Street implements JsonSerializable, Stringable
{
    private function __construct(private string $street)
    {
        Assert::notEmpty($street, 'Street must not be empty.');
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
