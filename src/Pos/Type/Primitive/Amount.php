<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;

final readonly class Amount implements JsonSerializable, Stringable
{
    private function __construct(private float $amount) {}


    public static function fromInt(int $amount): self
    {
        return new self((float) $amount);
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function jsonSerialize(): float
    {
        return $this->amount;
    }

    public function toFloat(): float
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }
}
