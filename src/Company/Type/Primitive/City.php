<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class City implements JsonSerializable, Stringable
{
    private function __construct(private string $city)
    {
        Assert::notEmpty($city, 'City must not be empty.');
        Assert::maxLength($city, 250, 'City must not exceed 250 characters.');
    }

    public static function fromString(string $city): self
    {
        return new self($city);
    }

    public function jsonSerialize(): string
    {
        return $this->city;
    }

    public function toString(): string
    {
        return $this->city;
    }

    public function __toString(): string
    {
        return $this->city;
    }
}
