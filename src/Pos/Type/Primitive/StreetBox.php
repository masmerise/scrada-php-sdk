<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class StreetBox implements JsonSerializable, Stringable
{
    private function __construct(private string $streetBox)
    {
        Assert::notEmpty($streetBox, 'Street box must not be empty.');
    }

    public static function fromString(string $streetBox): self
    {
        return new self($streetBox);
    }

    public function jsonSerialize(): string
    {
        return $this->streetBox;
    }

    public function toString(): string
    {
        return $this->streetBox;
    }

    public function __toString(): string
    {
        return $this->streetBox;
    }
}
