<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts\Request;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Pos\AddReceipts\AddReceipts;

/** @internal */
final class AddReceiptsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CompanyId $companyId,
        private readonly AddReceipts $data,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function resolveEndpoint(): string
    {
        $companyId = $this->companyId->toString();

        return "company/{$companyId}/pos/receipts";
    }
}
