<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Pos\Type\Receipts;
use Webmozart\Assert\Assert;

final readonly class AddReceipts
{
    private function __construct(
        public Receipts $receipts,
    ) {}

    public static function parameters(Receipts $receipts): self
    {
        Assert::notEmpty($receipts->all(), 'Receipts must not be empty.');

        return new self(receipts: $receipts);
    }

    /** @return array<int, array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(
            fn (Receipt $receipt) => $receipt->toArray(),
            $this->receipts->all()
        );
    }
}
