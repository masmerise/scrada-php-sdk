<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class VatPercentage implements JsonSerializable, Stringable
{
    private function __construct(private float $percentage)
    {
        Assert::greaterThanEq($percentage, 0, 'VAT percentage must be greater than or equal to 0.');
        Assert::lessThanEq($percentage, 100, 'VAT percentage must not exceed 100.');
    }

    public static function fromInt(int $percentage): self
    {
        return new self((float) $percentage);
    }

    public static function fromFloat(float $percentage): self
    {
        return new self($percentage);
    }

    public function jsonSerialize(): float
    {
        return $this->percentage;
    }

    public function toFloat(): float
    {
        return $this->percentage;
    }

    public function __toString(): string
    {
        return (string) $this->percentage;
    }
}
