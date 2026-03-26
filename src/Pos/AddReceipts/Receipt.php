<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Core\Type\Primitive\DateTimeWithOffset;
use Scrada\Pos\Type\Payments;
use Scrada\Pos\Type\Primitive\Amount;
use Scrada\Pos\Type\Primitive\LocationId;
use Scrada\Pos\Type\Primitive\LocationName;
use Scrada\Pos\Type\Primitive\Note;
use Scrada\Pos\Type\Primitive\PurchaseOrderReference;
use Scrada\Pos\Type\Primitive\ReceiptId;
use Scrada\Pos\Type\Primitive\ReceiptNumber;
use Scrada\Pos\Type\Primitive\RegisterId;
use Scrada\Pos\Type\Primitive\RegisterName;
use Scrada\Pos\Type\ReceiptLines;

final readonly class Receipt
{
    private function __construct(
        public DateTimeWithOffset $receiptCreatedOn,
        public ?ReceiptId $receiptID = null,
        public ?ReceiptNumber $number = null,
        public ?DateTimeWithOffset $receiptFinalizedOn = null,
        public ?bool $convertToInvoice = null,
        public ?LocationId $locationID = null,
        public ?LocationName $locationName = null,
        public ?RegisterId $registerID = null,
        public ?RegisterName $registerName = null,
        public ?Customer $customer = null,
        public ?Delivery $delivery = null,
        public ?PurchaseOrderReference $purchaseOrderReference = null,
        public ?Note $note = null,
        public ?Amount $totalInclVat = null,
        public ?Amount $totalTip = null,
        public ?ReceiptLines $lines = null,
        public ?Payments $payments = null,
    ) {}

    public static function parameters(
        DateTimeWithOffset $receiptCreatedOn,
        ?ReceiptId $receiptID = null,
        ?ReceiptNumber $number = null,
        ?DateTimeWithOffset $receiptFinalizedOn = null,
        ?bool $convertToInvoice = null,
        ?LocationId $locationID = null,
        ?LocationName $locationName = null,
        ?RegisterId $registerID = null,
        ?RegisterName $registerName = null,
        ?Customer $customer = null,
        ?Delivery $delivery = null,
        ?PurchaseOrderReference $purchaseOrderReference = null,
        ?Note $note = null,
        ?Amount $totalInclVat = null,
        ?Amount $totalTip = null,
        ?ReceiptLines $lines = null,
        ?Payments $payments = null,
    ): self {
        return new self(
            receiptCreatedOn: $receiptCreatedOn,
            receiptID: $receiptID,
            number: $number,
            receiptFinalizedOn: $receiptFinalizedOn,
            convertToInvoice: $convertToInvoice,
            locationID: $locationID,
            locationName: $locationName,
            registerID: $registerID,
            registerName: $registerName,
            customer: $customer,
            delivery: $delivery,
            purchaseOrderReference: $purchaseOrderReference,
            note: $note,
            totalInclVat: $totalInclVat,
            totalTip: $totalTip,
            lines: $lines,
            payments: $payments,
        );
    }

    /** @return array<string, bool|float|int|string|array<int|string, mixed>> */
    public function toArray(): array
    {
        return array_filter([
            'receiptID' => $this->receiptID?->toString(),
            'number' => $this->number?->toString(),
            'receiptCreatedOn' => $this->receiptCreatedOn->toString(),
            'receiptFinalizedOn' => $this->receiptFinalizedOn?->toString(),
            'convertToInvoice' => $this->convertToInvoice,
            'locationID' => $this->locationID?->toString(),
            'locationName' => $this->locationName?->toString(),
            'registerID' => $this->registerID?->toString(),
            'registerName' => $this->registerName?->toString(),
            'customer' => $this->customer?->toArray(),
            'delivery' => $this->delivery?->toArray(),
            'purchaseOrderReference' => $this->purchaseOrderReference?->toString(),
            'note' => $this->note?->toString(),
            'totalInclVat' => $this->totalInclVat?->toFloat(),
            'totalTip' => $this->totalTip?->toFloat(),
            'lines' => $this->lines !== null
                ? array_map(fn (ReceiptLine $line) => $line->toArray(), $this->lines->all())
                : null,
            'payments' => $this->payments !== null
                ? array_map(fn (Payment $payment) => $payment->toArray(), $this->payments->all())
                : null,
        ], fn (mixed $v): bool => $v !== null);
    }
}
