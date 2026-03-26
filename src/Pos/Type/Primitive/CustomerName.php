<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class CustomerName implements JsonSerializable, Stringable
{
    private function __construct(private string $name)
    {
        Assert::notEmpty($name, 'Customer name must not be empty.');
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
