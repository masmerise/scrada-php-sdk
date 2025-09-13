<?php declare(strict_types=1);

namespace Scrada\CashBook;

use Scrada\CashBook\All\Failure\CouldNotGetAllCashBooks;
use Scrada\CashBook\All\Request\GetAllCashBooksRequest;
use Scrada\CashBook\Type\CashBook;
use Scrada\CashBook\Type\Mapping\MapsCashBooks;
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Update\Failure\CouldNotUpdateCashBook;
use Scrada\CashBook\Update\Request\UpdateCashBookRequest;
use Scrada\CashBook\Update\UpdateCashBook;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Core\Failure\ScradaApiException;
use Scrada\Core\Failure\UnknownException;
use Scrada\Core\Http\ScradaResource;

/** @internal */
final readonly class CashBookResource extends ScradaResource
{
    use MapsCashBooks;

    /**
     * Get all cash books belonging to this company.
     *
     * @return array<CashBook>
     * @throws CouldNotGetAllCashBooks
     * @throws ScradaApiException
     * @throws UnknownException
     */
    public function all(CompanyId $companyId): array
    {
        $cashBooks = $this->send(
            request: new GetAllCashBooksRequest($companyId),
            onFailure: CouldNotGetAllCashBooks::class,
        );

        return array_map($this->toCashBook(...), $cashBooks);
    }

    /**
     * Update an existing cash book.
     *
     * If a property of the cash book is set null or a property is missing then the system assumes that this property must keep its original value.
     * Only in case of property endDate, if this property is missing or has has value null, the system assumes that it has value null.
     *
     * @throws CouldNotUpdateCashBook
     * @throws ScradaApiException
     * @throws UnknownException
     */
    public function update(CompanyId $companyId, CashBookId $cashBookId, UpdateCashBook $data): true
    {
        $this->send(
            request: new UpdateCashBookRequest($companyId, $cashBookId, $data),
            onFailure: CouldNotUpdateCashBook::class,
        );

        return true;
    }
}
