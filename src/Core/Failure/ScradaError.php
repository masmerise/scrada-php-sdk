<?php declare(strict_types=1);

namespace Scrada\Core\Failure;

final readonly class ScradaError
{
    public function __construct(
        /**
         * Error code.
         */
        public int $errorCode,

        /**
         * Localized error text.
         */
        public string $defaultFormat,

        /**
         * Inner errors.
         *
         * @var self[]
         */
        public array $innerErrors,
    ) {}
}
