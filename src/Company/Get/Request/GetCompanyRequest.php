<?php declare(strict_types=1);

namespace Scrada\Company\Get\Request;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Scrada\Company\Type\Primitive\CompanyId;

/** @internal */
final class GetCompanyRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly CompanyId $id) {}

    public function resolveEndpoint(): string
    {
        $id = $this->id->toString();

        return "company/{$id}";
    }
}
