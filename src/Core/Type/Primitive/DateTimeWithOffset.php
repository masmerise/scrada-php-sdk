<?php declare(strict_types=1);

namespace Scrada\Core\Type\Primitive;

use DateTimeImmutable;
use JsonSerializable;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class DateTimeWithOffset implements JsonSerializable, Stringable
{
    private function __construct(private string $datetime) {}

    public static function fromString(string $datetime): self
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $datetime);
        Assert::isInstanceOf($parsed, DateTimeImmutable::class, "{$datetime} could not be parsed. Must be ISO 8601 with timezone offset, e.g. 2026-03-01T09:15:00+01:00.");

        return new self($datetime);
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->datetime;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
