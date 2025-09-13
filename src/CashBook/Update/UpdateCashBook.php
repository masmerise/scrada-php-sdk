<?php declare(strict_types=1);

namespace Scrada\CashBook\Update;

use Scrada\CashBook\Type\Primitive\Balance;
use Scrada\CashBook\Type\Primitive\CodaFormat;
use Scrada\CashBook\Type\Primitive\CodaGenerationDayOfWeek;
use Scrada\CashBook\Type\Primitive\CodaGenerationPeriod;
use Scrada\CashBook\Type\Primitive\Name;
use Scrada\Core\Type\Primitive\Date;

final readonly class UpdateCashBook
{
    private function __construct(
        /**
         * Show the cash book in the dashboard.
         */
        public ?bool $active = null,

        /**
         * cash book name.
         */
        public ?Name $name = null,

        /**
         * The start date.
         */
        public ?Date $startDate = null,

        /**
         * The closing date.
         */
        public ?Date $endDate = null,

        /**
         * The start balance.
         */
        public ?Balance $startBalance = null,

        /**
         * Optional warning level for high balance.
         */
        public ?Balance $warnBalanceTooHigh = null,

        /**
         * The CODA format.
         */
        public ?CodaFormat $codaFileType = null,

        /**
         * The CODA generation period.
         */
        public ?CodaGenerationPeriod $codaGenerationPeriodType = null,

        /**
         * Day of the week to generate CODA file.
         */
        public ?CodaGenerationDayOfWeek $codaGenerationStartWeekDay = null,

        /**
         * Allow entries in the cash book after a CODA file has been generated. Only applicable when the cash book is linked to a journal.
         */
        public ?bool $allowEntryAfterCoda = null,

        /**
         * Add journal payment reference to CODA files.
         */
        public ?bool $addPaymentReference = null,
    ) {}

    public static function parameters(
        ?bool $active = null,
        ?Name $name = null,
        ?Date $startDate = null,
        ?Date $endDate = null,
        ?Balance $startBalance = null,
        ?Balance $warnBalanceTooHigh = null,
        ?CodaFormat $codaFileType = null,
        ?CodaGenerationPeriod $codaGenerationPeriodType = null,
        ?CodaGenerationDayOfWeek $codaGenerationStartWeekDay = null,
        ?bool $allowEntryAfterCoda = null,
        ?bool $addPaymentReference = null,
    ): self {
        return new self(
            active: $active,
            name: $name,
            startDate: $startDate,
            endDate: $endDate,
            startBalance: $startBalance,
            warnBalanceTooHigh: $warnBalanceTooHigh,
            codaFileType: $codaFileType,
            codaGenerationPeriodType: $codaGenerationPeriodType,
            codaGenerationStartWeekDay: $codaGenerationStartWeekDay,
            allowEntryAfterCoda: $allowEntryAfterCoda,
            addPaymentReference: $addPaymentReference,
        );
    }

    /**
     * @return array<string, bool|float|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'active' => $this->active,
            'name' => $this->name?->toString(),
            'startDate' => $this->startDate?->toString(),
            'endDate' => $this->endDate?->toString(),
            'startBalance' => $this->startBalance?->toFloat(),
            'warnBalanceTooHigh' => $this->warnBalanceTooHigh?->toFloat(),
            'codaFileType' => $this->codaFileType?->value,
            'codaGenerationPeriodType' => $this->codaGenerationPeriodType?->value,
            'codaGenerationStartWeekDay' => $this->codaGenerationStartWeekDay?->value,
            'allowEntryAfterCoda' => $this->allowEntryAfterCoda,
            'addPaymentReference' => $this->addPaymentReference,
        ];
    }
}
