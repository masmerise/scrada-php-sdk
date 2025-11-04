<?php declare(strict_types=1);

namespace Tests\Authentication;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Scrada\Authentication\Credentials;
use Scrada\Authentication\Failure\CouldNotAuthenticate;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Scrada;
use Tests\ScradaTest;

/** @mixin ScradaTest */
trait AuthenticationTests
{
    #[Group('authentication')]
    #[Test]
    public function failed_authentication(): void
    {
        // Assert
        $this->expectException(CouldNotAuthenticate::class);
        $this->expectExceptionMessage('The API Key and/or Password is wrong.');

        // Arrange
        $scrada = $this->newInstance('0f83133b-eadd-41a2-9879-a2dbd522c381', password: '<PASSWORD123456>');

        // Act
        $scrada->company->get(
            CompanyId::fromString('0f83133b-eadd-41a2-9879-a2dbd522c381')
        );
    }

    protected function newInstance(string $key, string $password): Scrada
    {
        return Scrada::authenticate(
            Credentials::present($key, $password)
        );
    }
}
