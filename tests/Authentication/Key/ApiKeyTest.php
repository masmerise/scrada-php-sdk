<?php declare(strict_types=1);

namespace Scrada\Tests\Authentication\Key;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Scrada\Authentication\Key\ApiKey;

#[Group('auth')]
final class ApiKeyTest extends TestCase
{
    #[Test]
    #[TestWith(['0f83133b-eadd-41a2-9879-a2dbd522c381'])]
    #[TestWith(['e9d61171-9263-40cf-835b-c119f740244d'])]
    #[TestWith(['c94bea44-8606-4fce-a619-1ae37888b626'])]
    public function valid(string $key): void
    {
        $key = ApiKey::fromString($key);

        $this->assertInstanceOf(ApiKey::class, $key);
    }

    #[Test]
    #[TestWith(['01994800-0f08-7c24-887f-7ac8d53ce32c'])]
    #[TestWith([''])]
    #[TestWith(['c94bea44-8606-4fce-a619'])]
    public function invalid(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The API key must be a valid UUIDv4.');

        ApiKey::fromString($key);
    }
}
