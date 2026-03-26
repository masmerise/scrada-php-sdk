<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Core\Type\Primitive\DateTimeWithOffset;
use Scrada\Pos\Type\Primitive\Amount;
use Scrada\Pos\Type\Primitive\GroupId;
use Scrada\Pos\Type\Primitive\GroupName;
use Scrada\Pos\Type\Primitive\ItemCode;
use Scrada\Pos\Type\Primitive\ItemId;
use Scrada\Pos\Type\Primitive\ItemName;
use Scrada\Pos\Type\Primitive\ItemType;
use Scrada\Pos\Type\Primitive\LineId;
use Scrada\Pos\Type\Primitive\Quantity;
use Scrada\Pos\Type\Primitive\UnitType;
use Scrada\Pos\Type\Primitive\VatPercentage;
use Scrada\Pos\Type\Primitive\VatType;

final readonly class ReceiptLine
{
    private function __construct(
        public ?LineId $lineID = null,
        public ?DateTimeWithOffset $orderDateTime = null,
        public ?Quantity $quantity = null,
        public ?UnitType $unitType = null,
        public ?Amount $itemInclVat = null,
        public ?VatType $vatType = null,
        public ?VatPercentage $vatPercentage = null,
        public ?Amount $totalDiscountInclVat = null,
        public ?Amount $totalInclVat = null,
        public ?ItemType $itemType = null,
        public ?ItemId $itemID = null,
        public ?ItemCode $itemCode = null,
        public ?ItemName $itemName = null,
        public ?GroupId $groupID = null,
        public ?GroupName $groupName = null,
    ) {}

    public static function parameters(
        ?LineId $lineID = null,
        ?DateTimeWithOffset $orderDateTime = null,
        ?Quantity $quantity = null,
        ?UnitType $unitType = null,
        ?Amount $itemInclVat = null,
        ?VatType $vatType = null,
        ?VatPercentage $vatPercentage = null,
        ?Amount $totalDiscountInclVat = null,
        ?Amount $totalInclVat = null,
        ?ItemType $itemType = null,
        ?ItemId $itemID = null,
        ?ItemCode $itemCode = null,
        ?ItemName $itemName = null,
        ?GroupId $groupID = null,
        ?GroupName $groupName = null,
    ): self {
        return new self(
            lineID: $lineID,
            orderDateTime: $orderDateTime,
            quantity: $quantity,
            unitType: $unitType,
            itemInclVat: $itemInclVat,
            vatType: $vatType,
            vatPercentage: $vatPercentage,
            totalDiscountInclVat: $totalDiscountInclVat,
            totalInclVat: $totalInclVat,
            itemType: $itemType,
            itemID: $itemID,
            itemCode: $itemCode,
            itemName: $itemName,
            groupID: $groupID,
            groupName: $groupName,
        );
    }

    /** @return array<string, float|int|string> */
    public function toArray(): array
    {
        return array_filter([
            'lineID' => $this->lineID?->toString(),
            'orderDateTime' => $this->orderDateTime?->toString(),
            'quantity' => $this->quantity?->toFloat(),
            'unitType' => $this->unitType?->value,
            'itemInclVat' => $this->itemInclVat?->toFloat(),
            'vatType' => $this->vatType?->value,
            'vatPercentage' => $this->vatPercentage?->toFloat(),
            'totalDiscountInclVat' => $this->totalDiscountInclVat?->toFloat(),
            'totalInclVat' => $this->totalInclVat?->toFloat(),
            'itemType' => $this->itemType?->value,
            'itemID' => $this->itemID?->toString(),
            'itemCode' => $this->itemCode?->toString(),
            'itemName' => $this->itemName?->toString(),
            'groupID' => $this->groupID?->toString(),
            'groupName' => $this->groupName?->toString(),
        ], static fn (mixed $v): bool => $v !== null);
    }
}
