<?php declare(strict_types=1);

namespace Tests\Company;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Scrada\Company\Type\Company;
use Scrada\Company\Type\Primitive\City;
use Scrada\Company\Type\Primitive\Code;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Company\Type\Primitive\CountryId;
use Scrada\Company\Type\Primitive\Email;
use Scrada\Company\Type\Primitive\InvoiceInfo;
use Scrada\Company\Type\Primitive\Name;
use Scrada\Company\Type\Primitive\Phone;
use Scrada\Company\Type\Primitive\ReportLanguageId;
use Scrada\Company\Type\Primitive\Street;
use Scrada\Company\Type\Primitive\ZipCode;
use Scrada\Company\Update\UpdateCompany;
use Tests\ScradaTest;

/** @mixin ScradaTest */
trait CompanyTests
{
    #[Group('company')]
    #[Test]
    public function get_company(): void
    {
        // Arrange
        $id = CompanyId::fromString($_ENV['COMPANY_ID']);

        // Act
        $company = $this->scrada->company->get($id);

        // Assert
        $this->assertInstanceOf(Company::class, $company);
    }

    #[Group('company')]
    #[Test]
    public function update_company(): void
    {
        // Arrange
        $id = CompanyId::fromString($_ENV['COMPANY_ID']);

        $data = UpdateCompany::parameters(
            active: true,
            code: Code::fromString('MAS'),
            name: Name::fromString('Masmerise'),
            email: Email::fromString('scrada@masmerise.be'),
            street: Street::fromString('Gaston Crommenlaan 9'),
            zipCode: ZipCode::fromString('9000'),
            city: City::fromString('Gent'),
            countryId: CountryId::Belgium,
            phone: Phone::fromString('+32 486 60 33 71'),
            reportLanguageId: ReportLanguageId::Dutch,
            invoiceInfo: InvoiceInfo::CompanyAddress,
        );

        // Act
        $result = $this->scrada->company->update(
            id: $id,
            data: $data,
        );

        // Assert
        $this->assertTrue($result);
    }
}
