<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Core\Type\Primitive\DateTimeWithOffset;
use Scrada\Pos\Type\Primitive\Amount;
use Scrada\Pos\Type\Primitive\PaymentId;
use Scrada\Pos\Type\Primitive\PaymentMethodId;
use Scrada\Pos\Type\Primitive\PaymentMethodName;
use Scrada\Pos\Type\Primitive\PaymentMethodType;
use Scrada\Pos\Type\Primitive\PaymentReference;
use Scrada\Pos\Type\Primitive\PaymentType;

final readonly class Payment
{
    private function __construct(
        public ?PaymentId $paymentID = null,
        public ?DateTimeWithOffset $paymentDateTime = null,
        public ?PaymentType $type = null,
        public ?Amount $amount = null,
        public ?PaymentMethodId $paymentMethodID = null,
        public ?PaymentMethodType $paymentMethodType = null,
        public ?PaymentMethodName $paymentMethodName = null,
        public ?PaymentReference $reference = null,
    ) {}

    public static function parameters(
        ?PaymentId $paymentID = null,
        ?DateTimeWithOffset $paymentDateTime = null,
        ?PaymentType $type = null,
        ?Amount $amount = null,
        ?PaymentMethodId $paymentMethodID = null,
        ?PaymentMethodType $paymentMethodType = null,
        ?PaymentMethodName $paymentMethodName = null,
        ?PaymentReference $reference = null,
    ): self {
        return new self(
            paymentID: $paymentID,
            paymentDateTime: $paymentDateTime,
            type: $type,
            amount: $amount,
            paymentMethodID: $paymentMethodID,
            paymentMethodType: $paymentMethodType,
            paymentMethodName: $paymentMethodName,
            reference: $reference,
        );
    }

    /** @return array<string, float|int|string|null> */
    public function toArray(): array
    {
        return [
            'paymentID' => $this->paymentID?->toString(),
            'paymentDateTime' => $this->paymentDateTime?->toString(),
            'type' => $this->type?->value,
            'amount' => $this->amount?->toFloat(),
            'paymentMethodID' => $this->paymentMethodID?->toString(),
            'paymentMethodType' => $this->paymentMethodType?->value,
            'paymentMethodName' => $this->paymentMethodName?->toString(),
            'reference' => $this->reference?->toString(),
        ];
    }
}
