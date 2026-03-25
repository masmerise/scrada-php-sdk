<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class LanguageCode implements JsonSerializable, Stringable
{
    private function __construct(private string $code)
    {
        Assert::notEmpty($code, 'Language code must not be empty.');
        Assert::length($code, 2, 'Language code must be exactly 2 characters.');
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function jsonSerialize(): string
    {
        return $this->code;
    }

    public function toString(): string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
