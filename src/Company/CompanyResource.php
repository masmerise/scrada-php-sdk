<?php declare(strict_types=1);

namespace Scrada\Company;

use Scrada\Company\Get\Failure\CouldNotGetCompany;
use Scrada\Company\Get\Request\GetCompanyRequest;
use Scrada\Company\Type\Company;
use Scrada\Company\Type\Mapping\MapsCompanies;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Company\Update\Failure\CouldNotUpdateCompany;
use Scrada\Company\Update\Request\UpdateCompanyRequest;
use Scrada\Company\Update\UpdateCompany;
use Scrada\Core\Failure\ScradaApiException;
use Scrada\Core\Failure\UnknownException;
use Scrada\Core\Http\ScradaResource;

/** @internal */
final readonly class CompanyResource extends ScradaResource
{
    use MapsCompanies;

    /**
     * Get the specified company.
     *
     * @throws CouldNotGetCompany
     * @throws ScradaApiException
     * @throws UnknownException
     */
    public function get(CompanyId $id): Company
    {
        $company = $this->send(
            request: new GetCompanyRequest($id),
            onFailure: CouldNotGetCompany::class,
        );

        return $this->toCompany($company);
    }

    /**
     * Update an existing company.
     *
     * If a property of the company is set null or a property is missing then the system assumes that this property must keep its original value.
     *
     * @throws CouldNotUpdateCompany
     * @throws ScradaApiException
     * @throws UnknownException
     */
    public function update(CompanyId $id, UpdateCompany $data): true
    {
        $this->send(
            request: new UpdateCompanyRequest($id, $data),
            onFailure: CouldNotUpdateCompany::class,
        );

        return true;
    }
}
