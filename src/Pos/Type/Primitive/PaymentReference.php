<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class PaymentReference implements JsonSerializable, Stringable
{
    private function __construct(private string $reference)
    {
        Assert::notEmpty($reference, 'Payment reference must not be empty.');
    }

    public static function fromString(string $reference): self
    {
        return new self($reference);
    }

    public function jsonSerialize(): string
    {
        return $this->reference;
    }

    public function toString(): string
    {
        return $this->reference;
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}
