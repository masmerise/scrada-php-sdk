<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Contact implements JsonSerializable, Stringable
{
    private function __construct(private string $contact)
    {
        Assert::notEmpty($contact, 'Contact must not be empty.');
    }

    public static function fromString(string $contact): self
    {
        return new self($contact);
    }

    public function jsonSerialize(): string
    {
        return $this->contact;
    }

    public function toString(): string
    {
        return $this->contact;
    }

    public function __toString(): string
    {
        return $this->contact;
    }
}
