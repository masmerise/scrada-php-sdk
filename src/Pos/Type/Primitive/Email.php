<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Email implements JsonSerializable, Stringable
{
    private function __construct(private string $email)
    {
        Assert::notEmpty($email, 'Email must not be empty.');
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function jsonSerialize(): string
    {
        return $this->email;
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
