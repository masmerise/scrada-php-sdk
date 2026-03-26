<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Pos\Type\Primitive\AccountingCode;
use Scrada\Pos\Type\Primitive\Contact;
use Scrada\Pos\Type\Primitive\CustomerId;
use Scrada\Pos\Type\Primitive\CustomerName;
use Scrada\Pos\Type\Primitive\Email;
use Scrada\Pos\Type\Primitive\GlnNumber;
use Scrada\Pos\Type\Primitive\LanguageCode;
use Scrada\Pos\Type\Primitive\PeppolId;
use Scrada\Pos\Type\Primitive\Phone;
use Scrada\Pos\Type\Primitive\VatNumber;

final readonly class Customer
{
    private function __construct(
        public ?CustomerId $customerID = null,
        public ?CustomerName $name = null,
        public ?AccountingCode $accountingCode = null,
        public ?LanguageCode $languageCode = null,
        public ?CustomerAddress $address = null,
        public ?Phone $phone = null,
        public ?Email $email = null,
        public ?Email $invoiceEmail = null,
        public ?Contact $contact = null,
        public ?VatNumber $vatNumber = null,
        public ?GlnNumber $glnNumber = null,
        public ?PeppolId $peppolID = null,
    ) {}

    public static function parameters(
        ?CustomerId $customerID = null,
        ?CustomerName $name = null,
        ?AccountingCode $accountingCode = null,
        ?LanguageCode $languageCode = null,
        ?CustomerAddress $address = null,
        ?Phone $phone = null,
        ?Email $email = null,
        ?Email $invoiceEmail = null,
        ?Contact $contact = null,
        ?VatNumber $vatNumber = null,
        ?GlnNumber $glnNumber = null,
        ?PeppolId $peppolID = null,
    ): self {
        return new self(
            customerID: $customerID,
            name: $name,
            accountingCode: $accountingCode,
            languageCode: $languageCode,
            address: $address,
            phone: $phone,
            email: $email,
            invoiceEmail: $invoiceEmail,
            contact: $contact,
            vatNumber: $vatNumber,
            glnNumber: $glnNumber,
            peppolID: $peppolID,
        );
    }

    /** @return array<string, string|array<string, string>> */
    public function toArray(): array
    {
        return array_filter([
            'customerID' => $this->customerID?->toString(),
            'name' => $this->name?->toString(),
            'accountingCode' => $this->accountingCode?->toString(),
            'languageCode' => $this->languageCode?->value,
            'address' => $this->address?->toArray(),
            'phone' => $this->phone?->toString(),
            'email' => $this->email?->toString(),
            'invoiceEmail' => $this->invoiceEmail?->toString(),
            'contact' => $this->contact?->toString(),
            'vatNumber' => $this->vatNumber?->toString(),
            'glnNumber' => $this->glnNumber?->toString(),
            'peppolID' => $this->peppolID?->toString(),
        ], static fn (mixed $v): bool => $v !== null);
    }
}
