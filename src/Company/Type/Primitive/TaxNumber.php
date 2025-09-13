<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class TaxNumber implements JsonSerializable, Stringable
{
    private function __construct(private string $taxNumber)
    {
        Assert::notEmpty($taxNumber, 'Tax number must not be empty.');
        Assert::maxLength($taxNumber, 30, 'Tax number must not exceed 30 characters.');
    }

    public static function fromString(string $taxNumber): self
    {
        return new self($taxNumber);
    }

    public function jsonSerialize(): string
    {
        return $this->taxNumber;
    }

    public function toString(): string
    {
        return $this->taxNumber;
    }

    public function __toString(): string
    {
        return $this->taxNumber;
    }
}
