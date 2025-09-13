<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Code implements JsonSerializable, Stringable
{
    private function __construct(private string $code)
    {
        Assert::notEmpty($code, 'Code must not be empty.');
        Assert::maxLength($code, 200, 'Code must not exceed 200 characters.');
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
