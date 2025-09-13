<?php declare(strict_types=1);

namespace Scrada\CashBook\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Iban implements JsonSerializable, Stringable
{
    private function __construct(private string $iban)
    {
        Assert::notEmpty($iban, 'IBAN must not be empty.');
        Assert::regex($iban, '/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', 'IBAN structure is invalid.');
    }

    public static function fromString(string $iban): self
    {
        return new self($iban);
    }

    public function jsonSerialize(): string
    {
        return $this->iban;
    }

    public function toString(): string
    {
        return $this->iban;
    }

    public function __toString(): string
    {
        return $this->iban;
    }
}
