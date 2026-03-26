<?php declare(strict_types=1);

namespace Scrada\Pos;

use Scrada\Authentication\Failure\CouldNotAuthenticate;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Core\Failure\UnknownException;
use Scrada\Core\Failure\ValidationException;
use Scrada\Core\Http\ScradaResource;
use Scrada\Pos\AddReceipts\AddReceipts;
use Scrada\Pos\AddReceipts\Failure\CouldNotAddReceipts;
use Scrada\Pos\AddReceipts\Request\AddReceiptsRequest;
use Scrada\Pos\Type\Primitive\ReceiptId;
use Scrada\Pos\Type\ReceiptIds;

/** @internal */
final readonly class PosResource extends ScradaResource
{
    /**
     * Submit POS receipts for a company.
     *
     * @param CompanyId $companyId The company ID.
     * @param AddReceipts $data The receipts to submit.
     *
     * @return ReceiptIds The created receipt IDs.
     *
     * @throws CouldNotAddReceipts
     * @throws CouldNotAuthenticate
     * @throws UnknownException
     * @throws ValidationException
     */
    public function addReceipts(CompanyId $companyId, AddReceipts $data): ReceiptIds
    {
        /** @var string[] $receiptIds */
        $receiptIds = $this->send(
            request: new AddReceiptsRequest($companyId, $data),
            onFailure: CouldNotAddReceipts::class,
        );

        return ReceiptIds::of(
            array_map(
                static fn (string $id) => ReceiptId::fromString($id),
                $receiptIds,
            )
        );
    }
}
