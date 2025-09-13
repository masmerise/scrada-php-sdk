<?php declare(strict_types=1);

namespace Tests\Authentication;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Scrada\Authentication\Key\Password;

#[Group('auth')]
final class PasswordTest extends TestCase
{
    #[Test]
    #[TestWith(['csCbssrRvj$K@_mx'])]
    #[TestWith(['EHk518Cf0PMrmh0F'])]
    #[TestWith(['w@nOzV6gZjDCfa6b'])]
    public function valid(string $password): void
    {
        $password = Password::fromString($password);

        $this->assertInstanceOf(Password::class, $password);
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['0PMrmh0F'])]
    #[TestWith(['w@nOzV6gZjDCfa6beu3ddjfc'])]
    public function invalid(string $password): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be exactly 16 characters long.');

        Password::fromString($password);
    }
}
