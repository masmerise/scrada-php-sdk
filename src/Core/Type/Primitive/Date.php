<?php declare(strict_types=1);

namespace Scrada\Core\Type\Primitive;

use DateTimeImmutable;
use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Date implements JsonSerializable, Stringable
{
    private function __construct(private string $date) {}

    public static function fromString(string $date): self
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        Assert::isInstanceOf($parsed, DateTimeImmutable::class, "{$date} could not be parsed as a valid date. It must be in the format YYYY-MM-DD.");

        return new self($date);
    }

    public static function fromTimestamp(string $date): self
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s', $date);
        Assert::isInstanceOf($parsed, DateTimeImmutable::class, "{$date} could not be parsed as a valid date. It must be in the format YYYY-MM-DDTHH:mm:ss.");

        return new self($parsed->format('Y-m-d'));
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->date;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
