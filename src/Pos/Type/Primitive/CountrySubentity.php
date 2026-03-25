<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class CountrySubentity implements JsonSerializable, Stringable
{
    private function __construct(private string $countrySubentity)
    {
        Assert::notEmpty($countrySubentity, 'Country subentity must not be empty.');
    }

    public static function fromString(string $countrySubentity): self
    {
        return new self($countrySubentity);
    }

    public function jsonSerialize(): string
    {
        return $this->countrySubentity;
    }

    public function toString(): string
    {
        return $this->countrySubentity;
    }

    public function __toString(): string
    {
        return $this->countrySubentity;
    }
}
