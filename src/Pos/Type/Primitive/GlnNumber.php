<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class GlnNumber implements JsonSerializable, Stringable
{
    private function __construct(private string $number)
    {
        Assert::notEmpty($number, 'GLN number must not be empty.');
    }

    public static function fromString(string $number): self
    {
        return new self($number);
    }

    public function jsonSerialize(): string
    {
        return $this->number;
    }

    public function toString(): string
    {
        return $this->number;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}
