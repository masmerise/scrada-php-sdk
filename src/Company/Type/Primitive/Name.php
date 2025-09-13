<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Name implements JsonSerializable, Stringable
{
    private function __construct(private string $name)
    {
        Assert::notEmpty($name, 'Name must not be empty.');
        Assert::maxLength($name, 200, 'Name must not exceed 200 characters.');
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
