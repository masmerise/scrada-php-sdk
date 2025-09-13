<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class ZipCode implements JsonSerializable, Stringable
{
    private function __construct(private string $zipCode)
    {
        Assert::notEmpty($zipCode, 'ZIP code must not be empty.');
        Assert::maxLength($zipCode, 50, 'ZIP code must not exceed 50 characters.');
    }

    public static function fromString(string $zipCode): self
    {
        return new self($zipCode);
    }

    public function jsonSerialize(): string
    {
        return $this->zipCode;
    }

    public function toString(): string
    {
        return $this->zipCode;
    }

    public function __toString(): string
    {
        return $this->zipCode;
    }
}
