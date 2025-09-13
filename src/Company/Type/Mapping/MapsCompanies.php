<?php declare(strict_types=1);

namespace Scrada\Company\Type\Mapping;

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
use Scrada\Company\Type\Primitive\TaxNumber;
use Scrada\Company\Type\Primitive\VatNumber;
use Scrada\Company\Type\Primitive\ZipCode;

use function Scrada\Core\Utility\transform;

/** @internal */
trait MapsCompanies
{
    /**
     * @param array{
     *     id: string,
     *     active: bool,
     *     code: ?string,
     *     name: string,
     *     email: string,
     *     street: string,
     *     zipCode: string,
     *     city: string,
     *     countryID: string,
     *     taxNumber: string,
     *     vatNumber: string,
     *     phone: ?string,
     *     reportLanguageID: string,
     *     invoiceInfo: int,
     *     invoiceEmail: ?string,
     *     invoiceStreet: ?string,
     *     invoiceZipCode: ?string,
     *     invoiceCity: ?string,
     *     invoiceCountryID: ?string,
     *     invoiceCompanyID: ?string,
     * } $company
     */
    private function toCompany(array $company): Company
    {
        return Company::parameters(
            id: CompanyId::fromString($company['id']),
            active: $company['active'],
            code: transform($company['code'], Code::fromString(...)),
            name: Name::fromString($company['name']),
            email: Email::fromString($company['email']),
            street: Street::fromString($company['street']),
            zipCode: ZipCode::fromString($company['zipCode']),
            city: City::fromString($company['city']),
            countryId: CountryId::from($company['countryID']),
            taxNumber: TaxNumber::fromString($company['taxNumber']),
            vatNumber: VatNumber::fromString($company['vatNumber']),
            phone: transform($company['phone'], Phone::fromString(...)),
            reportLanguageId: ReportLanguageId::from($company['reportLanguageID']),
            invoiceInfo: InvoiceInfo::from($company['invoiceInfo']),
            invoiceEmail: transform($company['invoiceEmail'], Email::fromString(...)),
            invoiceStreet: transform($company['invoiceStreet'], Street::fromString(...)),
            invoiceZipCode: transform($company['invoiceZipCode'], ZipCode::fromString(...)),
            invoiceCity: transform($company['invoiceCity'], City::fromString(...)),
            invoiceCountryId: transform($company['invoiceCountryID'], CountryId::from(...)),
            invoiceCompanyId: transform($company['invoiceCompanyID'], CompanyId::fromString(...)),
        );
    }
}
