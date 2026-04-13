<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;

final readonly class Quantity implements JsonSerializable, Stringable
{
    private function __construct(private float $quantity) {}

    public static function fromInt(int $quantity): self
    {
        return new self((float) $quantity);
    }

    public static function fromFloat(float $quantity): self
    {
        return new self($quantity);
    }

    public function jsonSerialize(): float
    {
        return $this->quantity;
    }

    public function toFloat(): float
    {
        return $this->quantity;
    }

    public function __toString(): string
    {
        return (string) $this->quantity;
    }
}
