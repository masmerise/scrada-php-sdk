<?php declare(strict_types=1);

namespace Scrada\Core\Utility;

use Closure;

/**
 * Transforms a given value using the provided transformer if the value is not null.
 *
 * @internal
 *
 * @template T
 * @template R
 * @param T|null $value The value to be transformed.
 * @param Closure(T): R $transformer A callback function to apply to the value.
 *
 * @return R|null The transformed value, or null if the input value is null.
 */
function transform(mixed $value, Closure $transformer): mixed
{
    if ($value !== null) {
        return $transformer($value);
    }

    return null;
}
