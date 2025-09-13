<?php declare(strict_types=1);

namespace Scrada\CashBook\Type;

use Scrada\CashBook\Type\Primitive\Balance;
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Type\Primitive\CodaFormat;
use Scrada\CashBook\Type\Primitive\CodaGenerationDayOfWeek;
use Scrada\CashBook\Type\Primitive\CodaGenerationPeriod;
use Scrada\CashBook\Type\Primitive\Iban;
use Scrada\CashBook\Type\Primitive\Name;
use Scrada\Core\Type\Primitive\Date;

final readonly class CashBook
{
    private function __construct(
        /**
         * The cash book ID.
         */
        public CashBookId $id,

        /**
         * Show the cash book in the dashboard.
         */
        public bool $active,

        /**
         * cash book name.
         */
        public Name $name,

        /**
         * The start date.
         */
        public Date $startDate,

        /**
         * The closing date.
         */
        public ?Date $endDate,

        /**
         * The IBAN number of the cash book.
         */
        public Iban $iban,

        /**
         * The start balance.
         */
        public Balance $startBalance,

        /**
         * The current balance.
         */
        public Balance $currentBalance,

        /**
         * The last line date.
         */
        public ?Date $lastLineDate,

        /**
         * Optional warning level for high balance.
         */
        public ?Balance $warnBalanceTooHigh,

        /**
         * The CODA format.
         */
        public CodaFormat $codaFileType,

        /**
         * The CODA generation period.
         */
        public CodaGenerationPeriod $codaGenerationPeriodType,

        /**
         * Day of the week to generate CODA file.
         */
        public ?CodaGenerationDayOfWeek $codaGenerationStartWeekDay,

        /**
         * Allow entries in the cash book after a CODA file has been generated. Only applicable when the cash book is linked to a journal.
         */
        public bool $allowEntryAfterCoda,

        /**
         * Add journal payment reference to CODA files.
         */
        public bool $addPaymentReference,

        /**
         * Scrada invoice send till this date.
         */
        public ?Date $invoicedTill,

        /**
         * Scrada invoice paid till this date.
         */
        public ?Date $paidTill,

        /**
         * Earliest possible date for a cash book entry.
         */
        public Date $minimumPossibleLineDate,

        /**
         * Latest possible date for a cash book entry.
         */
        public Date $maximumPossibleLineDate,
    ) {}

    public static function parameters(
        CashBookId $id,
        bool $active,
        Name $name,
        Date $startDate,
        ?Date $endDate,
        Iban $iban,
        Balance $startBalance,
        Balance $currentBalance,
        ?Date $lastLineDate,
        ?Balance $warnBalanceTooHigh,
        CodaFormat $codaFileType,
        CodaGenerationPeriod $codaGenerationPeriodType,
        ?CodaGenerationDayOfWeek $codaGenerationStartWeekDay,
        bool $allowEntryAfterCoda,
        bool $addPaymentReference,
        ?Date $invoicedTill,
        ?Date $paidTill,
        Date $minimumPossibleLineDate,
        Date $maximumPossibleLineDate,
    ): self {
        return new self(
            id: $id,
            active: $active,
            name: $name,
            startDate: $startDate,
            endDate: $endDate,
            iban: $iban,
            startBalance: $startBalance,
            currentBalance: $currentBalance,
            lastLineDate: $lastLineDate,
            warnBalanceTooHigh: $warnBalanceTooHigh,
            codaFileType: $codaFileType,
            codaGenerationPeriodType: $codaGenerationPeriodType,
            codaGenerationStartWeekDay: $codaGenerationStartWeekDay,
            allowEntryAfterCoda: $allowEntryAfterCoda,
            addPaymentReference: $addPaymentReference,
            invoicedTill: $invoicedTill,
            paidTill: $paidTill,
            minimumPossibleLineDate: $minimumPossibleLineDate,
            maximumPossibleLineDate: $maximumPossibleLineDate,
        );
    }
}
