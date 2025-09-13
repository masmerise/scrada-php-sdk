<?php declare(strict_types=1);

namespace Scrada\CashBook\Type\Mapping;

use Scrada\CashBook\Type\CashBook;
use Scrada\CashBook\Type\Primitive\Balance;
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Type\Primitive\CodaFormat;
use Scrada\CashBook\Type\Primitive\CodaGenerationDayOfWeek;
use Scrada\CashBook\Type\Primitive\CodaGenerationPeriod;
use Scrada\CashBook\Type\Primitive\Iban;
use Scrada\CashBook\Type\Primitive\Name;
use Scrada\Core\Type\Primitive\Date;

use function Scrada\Core\Utility\transform;

/** @internal */
trait MapsCashBooks
{
    /**
     * @param array{
     *     id: string,
     *     active: bool,
     *     name: string,
     *     startDate: string,
     *     endDate: ?string,
     *     iban: string,
     *     startBalance: float,
     *     currentBalance: float,
     *     lastLineDate: ?string,
     *     warnBalanceTooHigh: ?float,
     *     codaFileType: int,
     *     codaGenerationPeriodType: int,
     *     codaGenerationStartWeekDay: ?int,
     *     allowEntryAfterCoda: bool,
     *     addPaymentReference: bool,
     *     invoicedTill: ?string,
     *     paidTill: ?string,
     *     minimumPossibleLineDate: string,
     *     maximumPossibleLineDate: string,
     * } $cashBook
     */
    private function toCashBook(array $cashBook): CashBook
    {
        return CashBook::parameters(
            id: CashBookId::fromString($cashBook['id']),
            active: $cashBook['active'],
            name: Name::fromString($cashBook['name']),
            startDate: Date::fromTimestamp($cashBook['startDate']),
            endDate: transform($cashBook['endDate'], Date::fromTimestamp(...)),
            iban: Iban::fromString($cashBook['iban']),
            startBalance: Balance::fromFloat($cashBook['startBalance']),
            currentBalance: Balance::fromFloat($cashBook['currentBalance']),
            lastLineDate: transform($cashBook['lastLineDate'], Date::fromTimestamp(...)),
            warnBalanceTooHigh: transform($cashBook['warnBalanceTooHigh'], Balance::fromFloat(...)),
            codaFileType: CodaFormat::from($cashBook['codaFileType']),
            codaGenerationPeriodType: CodaGenerationPeriod::from($cashBook['codaGenerationPeriodType']),
            codaGenerationStartWeekDay: transform($cashBook['codaGenerationStartWeekDay'], CodaGenerationDayOfWeek::from(...)),
            allowEntryAfterCoda: $cashBook['allowEntryAfterCoda'],
            addPaymentReference: $cashBook['addPaymentReference'],
            invoicedTill: transform($cashBook['invoicedTill'], Date::fromTimestamp(...)),
            paidTill: transform($cashBook['paidTill'], Date::fromTimestamp(...)),
            minimumPossibleLineDate: Date::fromTimestamp($cashBook['minimumPossibleLineDate']),
            maximumPossibleLineDate: Date::fromTimestamp($cashBook['maximumPossibleLineDate']),
        );
    }
}
