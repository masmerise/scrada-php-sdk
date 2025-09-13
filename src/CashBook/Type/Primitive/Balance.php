<?php declare(strict_types=1);

namespace Scrada\CashBook\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Balance implements JsonSerializable, Stringable
{
    private function __construct(private float $balance)
    {
        Assert::greaterThanEq($balance, 0, 'Balance must be greater than or equal to 0.');
    }

    public static function fromInt(int $balance): self
    {
        return new self((float) $balance);
    }

    public static function fromFloat(float $balance): self
    {
        return new self($balance);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function jsonSerialize(): float
    {
        return $this->balance;
    }

    public function toFloat(): float
    {
        return $this->balance;
    }

    public function __toString(): string
    {
        return "€ {$this->balance}";
    }
}
