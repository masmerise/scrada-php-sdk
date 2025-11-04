<?php declare(strict_types=1);

namespace Scrada\Core\Http;

use JsonException;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Request;
use Scrada\Authentication\Failure\CouldNotAuthenticate;
use Scrada\Core\Failure\Mapping\MapsScradaErrors;
use Scrada\Core\Failure\ScradaApiException;
use Scrada\Core\Failure\UnknownException;

/** @internal */
abstract readonly class ScradaResource
{
    use MapsScradaErrors;

    public function __construct(protected ScradaConnector $client) {}

    /**
     * @param Request $request
     * @param class-string<ScradaApiException> $onFailure
     *
     * @throws CouldNotAuthenticate
     * @throws UnknownException
     * @throws ScradaApiException
     */
    protected function send(Request $request, string $onFailure): array
    {
        try {
            $response = $this->client->send($request);
        } catch (SaloonException $ex) {
            throw UnknownException::sorry($ex);
        }

        try {
            $data = $response->json();
        } catch (JsonException $ex) {
            throw UnknownException::sorry($ex);
        }

        if ($response->successful()) {
            return $data;
        }

        if ($response->status() === 401) {
            throw CouldNotAuthenticate::becauseTheApiKeyAndOrPasswordIsWrong();
        }

        throw new $onFailure($this->toScradaError($data));
    }
}
