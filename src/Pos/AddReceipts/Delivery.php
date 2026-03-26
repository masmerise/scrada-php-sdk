<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

final readonly class Delivery
{
    private function __construct(
        public ?DeliveryAddress $address = null,
    ) {}

    public static function parameters(
        ?DeliveryAddress $address = null,
    ): self {
        return new self(
            address: $address,
        );
    }

    /** @return array<string, array<string, string>> */
    public function toArray(): array
    {
        return array_filter([
            'address' => $this->address?->toArray(),
        ], fn (mixed $v): bool => $v !== null);
    }
}
