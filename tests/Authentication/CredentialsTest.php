<?php declare(strict_types=1);

namespace Scrada\Tests\Authentication;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Scrada\Authentication\Credentials;

#[Group('auth')]
final class CredentialsTest extends TestCase
{
    #[Test]
    public function hash(): void
    {
        $credentials = Credentials::present('0f83133b-eadd-41a2-9879-a2dbd522c381', 'csCbssrRvj$K@_mx');

        $hash = $credentials->toHash();

        $this->assertSame('bbd08e4b755ae307aaecf13d3452a5f4', $hash);
    }

    #[Test]
    #[TestWith(['0f83133b-eadd-41a2-9879-a2dbd522c381', 'csCbssrRvj$K@_mx'])]
    #[TestWith(['e9d61171-9263-40cf-835b-c119f740244d', 'EHk518Cf0PMrmh0F'])]
    #[TestWith(['c94bea44-8606-4fce-a619-1ae37888b626', 'w@nOzV6gZjDCfa6b'])]
    public function valid(string $key, string $password): void
    {
        $credentials = Credentials::present($key, $password);

        $this->assertInstanceOf(Credentials::class, $credentials);
    }

    #[Test]
    #[TestWith(['01994800-0f08-7c24-887f-7ac8d53ce32c', ''])]
    #[TestWith(['', '0PMrmh0F'])]
    #[TestWith(['c94bea44-8606-4fce-a619', 'w@nOzV6gZjDCfa6beu3ddjfc'])]
    public function invalid(string $key, string $password): void
    {
        $this->expectException(InvalidArgumentException::class);

        Credentials::present($key, $password);
    }
}
