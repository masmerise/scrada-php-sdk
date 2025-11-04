<?php declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Scrada\Scrada;
use Tests\Authentication\AuthenticationTests;
use Tests\CashBook\CashBookTests;
use Tests\Company\CompanyTests;
use Tests\Core\FailureTests;

final class ScradaTest extends TestCase
{
    use AuthenticationTests;
    use CashBookTests;
    use CompanyTests;
    use FailureTests;

    private Scrada $scrada;

    protected function setUp(): void
    {
        $env = Dotenv::createImmutable(__DIR__);
        $env->load();

        $this->scrada = $this->newInstance($_ENV['API_KEY'], $_ENV['PASSWORD'])->useTest();
    }
}
