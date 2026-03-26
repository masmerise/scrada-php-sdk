<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Note implements JsonSerializable, Stringable
{
    private function __construct(private string $note)
    {
        Assert::notEmpty($note, 'Note must not be empty.');
    }

    public static function fromString(string $note): self
    {
        return new self($note);
    }

    public function jsonSerialize(): string
    {
        return $this->note;
    }

    public function toString(): string
    {
        return $this->note;
    }

    public function __toString(): string
    {
        return $this->note;
    }
}
