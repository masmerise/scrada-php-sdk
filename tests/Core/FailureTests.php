<?php declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Company\Type\Primitive\VatNumber;
use Scrada\Company\Update\Failure\CouldNotUpdateCompany;
use Scrada\Company\Update\UpdateCompany;
use Tests\ScradaTest;

/** @mixin ScradaTest */
trait FailureTests
{
    #[Group('failure')]
    #[Test]
    public function api_errors(): void
    {
        // Assert
        $this->expectException(CouldNotUpdateCompany::class);

        // Arrange
        $id = CompanyId::fromString($_ENV['COMPANY_ID']);

        $data = UpdateCompany::parameters(vatNumber: VatNumber::fromString('BE0836836222'));

        // Act
        $this->scrada->company->update(id: $id, data: $data);
    }
}
