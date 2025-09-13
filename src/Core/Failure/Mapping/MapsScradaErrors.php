<?php declare(strict_types=1);

namespace Scrada\Core\Failure\Mapping;

use Scrada\Core\Failure\ScradaError;
use Webmozart\Assert\Assert;

/** @internal */
trait MapsScradaErrors
{
    /**
     * @param array{
     *     errorCode: int,
     *     errorType: int,
     *     defaultFormat: string,
     *     parameters: string[],
     *     innerErrors: array,
     * } $error
     *
     * @return ScradaError
     */
    protected function toScradaError(array $error): ScradaError
    {
        Assert::keyExists($error, 'errorCode', 'Error code is missing.');
        Assert::keyExists($error, 'errorType', 'Error type is missing.');
        Assert::keyExists($error, 'defaultFormat', 'Default error text is missing.');
        Assert::keyExists($error, 'parameters', 'Error parameters are missing.');
        Assert::keyExists($error, 'innerErrors', 'Inner errors are missing.');

        return new ScradaError(
            $error['errorCode'],
            $error['errorType'],
            $error['defaultFormat'],
            $error['parameters'],
            array_map($this->toScradaError(...), $error['innerErrors']),
        );
    }
}
