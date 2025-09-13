<?php declare(strict_types=1);

namespace Scrada\Company\Update;

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

final readonly class UpdateCompany
{
    private function __construct(
        /**
         * Show the company in the company overview screen.
         */
        public ?bool $active = null,

        /**
         * The company code. Is used by Scrada as parameter in emails or integrations.
         */
        public ?Code $code = null,

        /**
         * The company name.
         */
        public ?Name $name = null,

        /**
         * The company email.
         */
        public ?Email $email = null,

        /**
         * The company street.
         */
        public ?Street $street = null,

        /**
         * The company ZIP code.
         */
        public ?ZipCode $zipCode = null,

        /**
         * The company city.
         */
        public ?City $city = null,

        /**
         * The company country ID.
         *
         * 12b741b9-c0ad-42b5-8471-e720763f3227: België/Belgique
         * 97e88925-b346-4b76-b671-ef1cf7d68733: Nederland
         * 74a68d03-8c08-4074-a41e-59d830024344: France
         * 597fde4e-a4db-42ac-99e1-af3962bbffa4: Deutschland
         */
        public ?CountryId $countryId = null,

        /**
         * The company tax number.
         *
         * België/Belgique: ondernemingsnummer/numéro d’Entreprise
         * Nederland: KvK nummer
         * France: SIRENE
         * Deutschland: Handelsregisternummer
         */
        public ?TaxNumber $taxNumber = null,

        /**
         * The company VAT number.
         */
        public ?VatNumber $vatNumber = null,

        /**
         * The company phone number.
         */
        public ?Phone $phone = null,

        /**
         * The language ID in which language the company reports (like daily receipt invoice) need to be generated in.
         *
         * e1e8395c-35b3-4282-89db-3feeaacc23bd: Nederlands (nl-BE)
         * 5300381e-f434-4e01-a1f8-53e7676d4cac: Français (fr-BE)
         * 68f89f67-b153-43e6-b9a6-f8d73b56a67a: English (en-US)
         */
        public ?ReportLanguageId $reportLanguageId = null,

        /**
         * The invoice information to use.
         */
        public ?InvoiceInfo $invoiceInfo = null,

        /**
         * The email address to receive the invoices on.
         * If not provided the invoice will be send to the company email.
         * Not applicable when 'invoiceInfo' is set to `3`.
         */
        public ?Email $invoiceEmail = null,

        /**
         * The invoice street. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?Street $invoiceStreet = null,

        /**
         * The invoice ZIP code. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?ZipCode $invoiceZipCode = null,

        /**
         * The invoice city. Only applicable when 'invoiceInfo' is set to `2`.
         */
        public ?City $invoiceCity = null,

        /**
         * The invoice country ID. Only applicable when 'invoiceInfo' is set to `2`.
         *
         * 12b741b9-c0ad-42b5-8471-e720763f3227: België/Belgique
         * 97e88925-b346-4b76-b671-ef1cf7d68733: Nederland
         * 74a68d03-8c08-4074-a41e-59d830024344: France
         * 597fde4e-a4db-42ac-99e1-af3962bbffa4: Deutschland
         */
        public ?CountryId $invoiceCountryId = null,

        /**
         * The company ID to which the invoice need to be send. Only applicable when 'invoiceInfo' is set to `3`.
         *
         * Remark: To set a different invoice company the user needs to have access rights to both companies.
         * The API credentials only provide access to a single company = null, it is therefore not possible to change this setting using the external API.
         */
        public ?CompanyId $invoiceCompanyId = null,
    ) {}

    public static function parameters(
        ?bool $active = null,
        ?Code $code = null,
        ?Name $name = null,
        ?Email $email = null,
        ?Street $street = null,
        ?ZipCode $zipCode = null,
        ?City $city = null,
        ?CountryId $countryId = null,
        ?TaxNumber $taxNumber = null,
        ?VatNumber $vatNumber = null,
        ?Phone $phone = null,
        ?ReportLanguageId $reportLanguageId = null,
        ?InvoiceInfo $invoiceInfo = null,
        ?Email $invoiceEmail = null,
        ?Street $invoiceStreet = null,
        ?ZipCode $invoiceZipCode = null,
        ?City $invoiceCity = null,
        ?CountryId $invoiceCountryId = null,
        ?CompanyId $invoiceCompanyId = null,
    ): self {
        return new self(
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

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'active' => $this->active,
            'code' => $this->code?->toString(),
            'name' => $this->name?->toString(),
            'email' => $this->email?->toString(),
            'street' => $this->street?->toString(),
            'zipCode' => $this->zipCode?->toString(),
            'city' => $this->city?->toString(),
            'countryID' => $this->countryId?->value,
            'taxNumber' => $this->taxNumber?->toString(),
            'vatNumber' => $this->vatNumber?->toString(),
            'phone' => $this->phone?->toString(),
            'reportLanguageID' => $this->reportLanguageId?->value,
            'invoiceInfo' => $this->invoiceInfo?->value,
            'invoiceEmail' => $this->invoiceEmail?->toString(),
            'invoiceStreet' => $this->invoiceStreet?->toString(),
            'invoiceZipCode' => $this->invoiceZipCode?->toString(),
            'invoiceCity' => $this->invoiceCity?->toString(),
            'invoiceCountryID' => $this->invoiceCountryId?->value,
            'invoiceCompanyID' => $this->invoiceCompanyId?->toString(),
        ];
    }
}
