<?php declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Scrada\Authentication\Credentials;
use Scrada\Scrada;
use Tests\CashBook\CashBookTests;
use Tests\Company\CompanyTests;
use Tests\Core\FailureTests;

final class ScradaTest extends TestCase
{
    use CashBookTests;
    use CompanyTests;
    use FailureTests;

    private Scrada $scrada;

    protected function setUp(): void
    {
        $env = Dotenv::createImmutable(__DIR__);
        $env->load();

        $this->scrada = Scrada::authenticate(
            Credentials::present(key: $_ENV['API_KEY'], password: $_ENV['PASSWORD'])
        )->useTest();
    }
}
