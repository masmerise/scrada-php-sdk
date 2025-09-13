<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class CompanyId implements JsonSerializable, Stringable
{
    private function __construct(private string $id)
    {
        Assert::uuid($id, 'Company ID must be a valid UUIDv4.');
        Assert::same(substr($id, 14, 1), '4', 'Company ID must be a valid UUIDv4.');
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function jsonSerialize(): string
    {
        return $this->id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
