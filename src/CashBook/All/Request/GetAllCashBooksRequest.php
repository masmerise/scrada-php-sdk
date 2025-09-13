<?php declare(strict_types=1);

namespace Scrada\CashBook\All\Request;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Scrada\Company\Type\Primitive\CompanyId;

/** @internal */
final class GetAllCashBooksRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly CompanyId $companyId) {}

    public function resolveEndpoint(): string
    {
        $companyId = $this->companyId->toString();

        return "company/{$companyId}/cashBook";
    }
}
