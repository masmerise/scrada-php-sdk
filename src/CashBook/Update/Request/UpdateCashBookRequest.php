<?php declare(strict_types=1);

namespace Scrada\CashBook\Update\Request;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Update\UpdateCashBook;
use Scrada\Company\Type\Primitive\CompanyId;

/** @internal */
final class UpdateCashBookRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly CompanyId $companyId,
        private readonly CashBookId $cashBookId,
        private readonly UpdateCashBook $data,
    ) {}

    /**
     * @return array<string, bool|float|int|string|null>
     */
    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function resolveEndpoint(): string
    {
        $companyId = $this->companyId->toString();
        $cashBookId = $this->cashBookId->toString();

        return "company/{$companyId}/cashBook/{$cashBookId}";
    }
}
