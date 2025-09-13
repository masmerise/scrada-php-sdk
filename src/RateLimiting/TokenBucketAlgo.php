<?php declare(strict_types=1);

namespace Scrada\RateLimiting;

use Saloon\RateLimitPlugin\Limit;

/** @internal */
final readonly class TokenBucketAlgo
{
    /** @return array<Limit> */
    public static function define(): array
    {
        return [
            Limit::allow(10)->everySeconds(10),
            Limit::allow(60)->everyMinute(),
        ];
    }
}
