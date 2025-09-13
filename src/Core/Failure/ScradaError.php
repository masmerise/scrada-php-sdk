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
         * Error type.
         */
        public int $errorType,

        /**
         * Localized error text.
         */
        public string $defaultFormat,

        /**
         * Error parameters.
         *
         * @var string[]
         */
        public array $parameters,

        /**
         * Inner errors.
         *
         * @var self[]
         */
        public array $innerErrors,
    ) {}
}
