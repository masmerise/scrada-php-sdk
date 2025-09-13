<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class VatNumber implements JsonSerializable, Stringable
{
    private function __construct(private string $vatNumber)
    {
        Assert::notEmpty($vatNumber, 'VAT number must not be empty.');
        Assert::maxLength($vatNumber, 30, 'VAT number must not exceed 30 characters.');
    }

    public static function fromString(string $vatNumber): self
    {
        return new self($vatNumber);
    }

    public function jsonSerialize(): string
    {
        return $this->vatNumber;
    }

    public function toString(): string
    {
        return $this->vatNumber;
    }

    public function __toString(): string
    {
        return $this->vatNumber;
    }
}
