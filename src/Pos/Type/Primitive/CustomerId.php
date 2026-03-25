<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class CustomerId implements JsonSerializable, Stringable
{
    private function __construct(private string $id)
    {
        Assert::notEmpty($id, 'Customer ID must not be empty.');
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function jsonSerialize(): string
    {
        return $this->id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
