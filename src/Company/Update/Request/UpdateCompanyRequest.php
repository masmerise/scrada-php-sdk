<?php declare(strict_types=1);

namespace Scrada\Company\Update\Request;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Company\Update\UpdateCompany;

/** @internal */
final class UpdateCompanyRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly CompanyId $companyId,
        private readonly UpdateCompany $data,
    ) {}

    /**
     * @return array<string, bool|int|string|null>
     */
    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function resolveEndpoint(): string
    {
        $companyId = $this->companyId->toString();

        return "company/{$companyId}";
    }
}
