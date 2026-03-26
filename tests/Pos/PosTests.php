<?php declare(strict_types=1);

namespace Scrada\Tests\Pos;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Core\Type\Primitive\DateTimeWithOffset;
use Scrada\Pos\AddReceipts\AddReceipts;
use Scrada\Pos\AddReceipts\Customer;
use Scrada\Pos\AddReceipts\CustomerAddress;
use Scrada\Pos\AddReceipts\Delivery;
use Scrada\Pos\AddReceipts\DeliveryAddress;
use Scrada\Pos\AddReceipts\Failure\CouldNotAddReceipts;
use Scrada\Pos\AddReceipts\Payment;
use Scrada\Pos\AddReceipts\Receipt;
use Scrada\Pos\AddReceipts\ReceiptLine;
use Scrada\Pos\Type\Payments;
use Scrada\Pos\Type\Primitive\Amount;
use Scrada\Pos\Type\Primitive\City;
use Scrada\Pos\Type\Primitive\CountryCode;
use Scrada\Pos\Type\Primitive\CustomerName;
use Scrada\Pos\Type\Primitive\ItemName;
use Scrada\Pos\Type\Primitive\ItemType;
use Scrada\Pos\Type\Primitive\PaymentMethodName;
use Scrada\Pos\Type\Primitive\PaymentMethodType;
use Scrada\Pos\Type\Primitive\PaymentType;
use Scrada\Pos\Type\Primitive\Quantity;
use Scrada\Pos\Type\Primitive\ReceiptId;
use Scrada\Pos\Type\Primitive\ReceiptNumber;
use Scrada\Pos\Type\Primitive\Street;
use Scrada\Pos\Type\Primitive\StreetNumber;
use Scrada\Pos\Type\Primitive\UnitType;
use Scrada\Pos\Type\Primitive\VatNumber;
use Scrada\Pos\Type\Primitive\VatPercentage;
use Scrada\Pos\Type\Primitive\VatType;
use Scrada\Pos\Type\Primitive\ZipCode;
use Scrada\Pos\Type\ReceiptLines;
use Scrada\Pos\Type\Receipts;
use Scrada\Tests\ScradaTest;

/** @mixin ScradaTest */
trait PosTests
{
    #[Group('pos')]
    #[Test]
    public function add_minimal_receipt(): void
    {
        // Arrange
        $companyId = CompanyId::fromString($_ENV['COMPANY_ID']);

        $data = AddReceipts::parameters(
            Receipts::including(
                Receipt::parameters(
                    receiptCreatedOn: DateTimeWithOffset::fromString('2026-03-25T10:00:00+01:00'),
                    number: ReceiptNumber::fromString('TEST-001'),
                ),
            ),
        );

        // Act
        $receiptIds = $this->scrada->pos->addReceipts($companyId, $data);

        // Assert
        $this->assertNotEmpty($receiptIds->all());
        $this->assertContainsOnlyInstancesOf(ReceiptId::class, $receiptIds);
    }

    #[Group('pos')]
    #[Test]
    public function add_full_receipt(): void
    {
        // Arrange
        $companyId = CompanyId::fromString($_ENV['COMPANY_ID']);

        $data = AddReceipts::parameters(
            Receipts::including(
                Receipt::parameters(
                    receiptCreatedOn: DateTimeWithOffset::fromString('2026-03-25T10:00:00+01:00'),
                    number: ReceiptNumber::fromString('TEST-002'),
                    customer: Customer::parameters(
                        name: CustomerName::fromString('Jan Janssen'),
                        address: CustomerAddress::parameters(
                            street: Street::fromString('Gaston Crommenlaan'),
                            streetNumber: StreetNumber::fromString('9'),
                            city: City::fromString('Gent'),
                            zipCode: ZipCode::fromString('9000'),
                            countryCode: CountryCode::Belgium,
                        ),
                        vatNumber: VatNumber::fromString('BE0836836234'),
                    ),
                    delivery: Delivery::parameters(
                        address: DeliveryAddress::parameters(
                            street: Street::fromString('Gaston Crommenlaan'),
                            streetNumber: StreetNumber::fromString('9'),
                            city: City::fromString('Gent'),
                            zipCode: ZipCode::fromString('9000'),
                            countryCode: CountryCode::Belgium,
                        ),
                    ),
                    totalInclVat: Amount::fromFloat(12.18),
                    lines: ReceiptLines::including(
                        ReceiptLine::parameters(
                            quantity: Quantity::fromInt(2),
                            unitType: UnitType::Piece,
                            itemInclVat: Amount::fromFloat(4.84),
                            vatType: VatType::Standard,
                            vatPercentage: VatPercentage::fromInt(21),
                            totalInclVat: Amount::fromFloat(9.68),
                            itemType: ItemType::Product,
                            itemName: ItemName::fromString('Friet speciaal'),
                        ),
                        ReceiptLine::parameters(
                            quantity: Quantity::fromInt(1),
                            unitType: UnitType::Piece,
                            itemInclVat: Amount::fromFloat(2.50),
                            vatType: VatType::Standard,
                            vatPercentage: VatPercentage::fromInt(6),
                            totalInclVat: Amount::fromFloat(2.50),
                            itemType: ItemType::Product,
                            itemName: ItemName::fromString('Frisdrank'),
                        ),
                    ),
                    payments: Payments::including(
                        Payment::parameters(
                            type: PaymentType::Payment,
                            amount: Amount::fromFloat(10.00),
                            paymentMethodType: PaymentMethodType::Cash,
                            paymentMethodName: PaymentMethodName::fromString('Cash'),
                        ),
                        Payment::parameters(
                            type: PaymentType::Payment,
                            amount: Amount::fromFloat(2.18),
                            paymentMethodType: PaymentMethodType::Bancontact,
                            paymentMethodName: PaymentMethodName::fromString('Bancontact'),
                        ),
                    ),
                ),
            ),
        );

        // Act
        $receiptIds = $this->scrada->pos->addReceipts($companyId, $data);

        // Assert
        $this->assertNotEmpty($receiptIds->all());
        $this->assertContainsOnlyInstancesOf(ReceiptId::class, $receiptIds);
    }

    #[Group('pos')]
    #[Test]
    public function cannot_add_empty_receipts(): void
    {
        // Assert
        $this->expectException(CouldNotAddReceipts::class);

        // Arrange
        $companyId = CompanyId::fromString($_ENV['COMPANY_ID']);
        $data = AddReceipts::parameters(Receipts::empty());

        // Act
        $this->scrada->pos->addReceipts($companyId, $data);
    }
}
