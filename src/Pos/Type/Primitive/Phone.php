<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Phone implements JsonSerializable, Stringable
{
    private function __construct(private string $phone)
    {
        Assert::notEmpty($phone, 'Phone must not be empty.');
    }

    public static function fromString(string $phone): self
    {
        return new self($phone);
    }

    public function jsonSerialize(): string
    {
        return $this->phone;
    }

    public function toString(): string
    {
        return $this->phone;
    }

    public function __toString(): string
    {
        return $this->phone;
    }
}
