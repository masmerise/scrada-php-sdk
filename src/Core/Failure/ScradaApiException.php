<?php declare(strict_types=1);

namespace Scrada\Core\Failure;

abstract class ScradaApiException extends ScradaException
{
    final public function __construct(public readonly ScradaError $error)
    {
        parent::__construct($error->defaultFormat, $error->errorCode);
    }
}
