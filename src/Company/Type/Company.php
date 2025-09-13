<?php declare(strict_types=1);

namespace Scrada\Company\Type;

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

final readonly class Company
{
    private function __construct(
        /**
         * The company ID.
         */
        public CompanyId $id,

        /**
         * Show the company in the company overview screen.
         */
        public bool $active,

        /**
         * The company code. Is used by Scrada as parameter in emails or integrations.
         */
        public ?Code $code,

        /**
         * The company name.
         */
        public Name $name,

        /**
         * The company email.
         */
        public Email $email,

        /**
         * The company street.
         */
        public Street $street,

        /**
         * The company ZIP code.
         */
        public ZipCode $zipCode,

        /**
         * The company city.
         */
        public City $city,

        /**
         * The company country ID.
         *
         * 12b741b9-c0ad-42b5-8471-e720763f3227: België/Belgique
         * 97e88925-b346-4b76-b671-ef1cf7d68733: Nederland
         * 74a68d03-8c08-4074-a41e-59d830024344: France
         * 597fde4e-a4db-42ac-99e1-af3962bbffa4: Deutschland
         */
        public CountryId $countryId,

        /**
         * The company tax number.
         *
         * België/Belgique: ondernemingsnummer/numéro d’Entreprise
         * Nederland: KvK nummer
         * France: SIRENE
         * Deutschland: Handelsregisternummer
         */
        public TaxNumber $taxNumber,

        /**
         * The company VAT number.
         */
        public VatNumber $vatNumber,

        /**
         * The company phone number.
         */
        public ?Phone $phone,

        /**
         * The language ID in which language the company reports (like daily receipt invoice) need to be generated in.
         *
         * e1e8395c-35b3-4282-89db-3feeaacc23bd: Nederlands (nl-BE)
         * 5300381e-f434-4e01-a1f8-53e7676d4cac: Français (fr-BE)
         * 68f89f67-b153-43e6-b9a6-f8d73b56a67a: English (en-US)
         */
        public ReportLanguageId $reportLanguageId,

        /**
         * The invoice information to use.
         */
        public InvoiceInfo $invoiceInfo,

        /**
         * The email address to receive the invoices on.
         * If not provided the invoice will be send to the company email.
         * Not applicable when 'invoiceInfo' is set to `3`.
         */
        public ?Email $invoiceEmail,

        /**
         * The invoice street. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?Street $invoiceStreet,

        /**
         * The invoice ZIP code. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?ZipCode $invoiceZipCode,

        /**
         * The invoice city. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?City $invoiceCity,

        /**
         * The invoice country ID. Only applicable when 'invoiceInfo' is set to `2`.
         *
         * 12b741b9-c0ad-42b5-8471-e720763f3227: België/Belgique
         * 97e88925-b346-4b76-b671-ef1cf7d68733: Nederland
         * 74a68d03-8c08-4074-a41e-59d830024344: France
         * 597fde4e-a4db-42ac-99e1-af3962bbffa4: Deutschland
         */
        public ?CountryId $invoiceCountryId,

        /**
         * The company ID to which the invoice need to be send. Only applicable when 'invoiceInfo' is set to `3`.
         *
         * Remark: To set a different invoice company the user needs to have access rights to both companies.
         * The API credentials only provide access to a single company, it is therefore not possible to change this setting using the external API.
         */
        public ?CompanyId $invoiceCompanyId,
    ) {}

    public static function parameters(
        CompanyId $id,
        bool $active,
        ?Code $code,
        Name $name,
        Email $email,
        Street $street,
        ZipCode $zipCode,
        City $city,
        CountryId $countryId,
        TaxNumber $taxNumber,
        VatNumber $vatNumber,
        ?Phone $phone,
        ReportLanguageId $reportLanguageId,
        InvoiceInfo $invoiceInfo,
        ?Email $invoiceEmail,
        ?Street $invoiceStreet,
        ?ZipCode $invoiceZipCode,
        ?City $invoiceCity,
        ?CountryId $invoiceCountryId,
        ?CompanyId $invoiceCompanyId,
    ): self {
        return new self(
            id: $id,
            active: $active,
            code: $code,
            name: $name,
            email: $email,
            street: $street,
            zipCode: $zipCode,
            city: $city,
            countryId: $countryId,
            taxNumber: $taxNumber,
            vatNumber: $vatNumber,
            phone: $phone,
            reportLanguageId: $reportLanguageId,
            invoiceInfo: $invoiceInfo,
            invoiceEmail: $invoiceEmail,
            invoiceStreet: $invoiceStreet,
            invoiceZipCode: $invoiceZipCode,
            invoiceCity: $invoiceCity,
            invoiceCountryId: $invoiceCountryId,
            invoiceCompanyId: $invoiceCompanyId,
        );
    }
}
