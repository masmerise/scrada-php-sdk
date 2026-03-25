<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class PurchaseOrderReference implements JsonSerializable, Stringable
{
    private function __construct(private string $reference)
    {
        Assert::notEmpty($reference, 'Purchase order reference must not be empty.');
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
