<?php declare(strict_types=1);

namespace Scrada\Environments;

/** @internal */
enum Environment
{
    case Production;
    case Test;

    public function url(): string
    {
        return match ($this) {
            self::Test => 'https://apitest.scrada.be/v1',
            default => 'https://api.scrada.be/v1',
        };
    }
}
