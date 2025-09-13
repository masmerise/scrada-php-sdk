<?php declare(strict_types=1);

namespace Scrada\Core\Failure;

use Exception;

final class UnknownException extends ScradaException
{
    public static function sorry(Exception $previous): self
    {
        return new self($previous->getMessage(), $previous->getCode(), $previous);
    }
}
