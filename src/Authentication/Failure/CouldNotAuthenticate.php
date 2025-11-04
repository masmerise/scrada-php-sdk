<?php declare(strict_types=1);

namespace Scrada\Authentication\Failure;

use Scrada\Core\Failure\ScradaException;

final class CouldNotAuthenticate extends ScradaException
{
    public static function becauseTheApiKeyAndOrPasswordIsWrong(): self
    {
        return new self('The API Key and/or Password is wrong.');
    }
}
