<?php declare(strict_types=1);

namespace Tests\CashBook;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Scrada\CashBook\Type\CashBook;
use Scrada\CashBook\Type\Primitive\Balance;
use Scrada\CashBook\Type\Primitive\CashBookId;
use Scrada\CashBook\Type\Primitive\CodaFormat;
use Scrada\CashBook\Type\Primitive\CodaGenerationDayOfWeek;
use Scrada\CashBook\Type\Primitive\CodaGenerationPeriod;
use Scrada\CashBook\Type\Primitive\Name;
use Scrada\CashBook\Update\UpdateCashBook;
use Scrada\Company\Type\Primitive\CompanyId;
use Scrada\Core\Type\Primitive\Date;
use Tests\ScradaTest;

/** @mixin ScradaTest */
trait CashBookTests
{
    #[Group('cashBook')]
    #[Test]
    public function get_all_cash_books(): void
    {
        // Arrange
        $companyId = CompanyId::fromString($_ENV['COMPANY_ID']);

        // Act
        $cashBooks = $this->scrada->cashBook->all($companyId);

        // Assert
        $this->assertIsArray($cashBooks);
        $this->assertContainsOnlyInstancesOf(CashBook::class, $cashBooks);
    }

    #[Group('cashBook')]
    #[Test]
    public function update_cash_book(): void
    {
        // Arrange
        $companyId = CompanyId::fromString($_ENV['COMPANY_ID']);
        $cashBookId = CashBookId::fromString($_ENV['CASH_BOOK_ID']);

        $data = UpdateCashBook::parameters(
            active: true,
            name: Name::fromString('Scrada PHP SDK'),
            startDate: Date::fromString('2020-01-01'),
            endDate: Date::fromString('2020-12-31'),
            startBalance: Balance::zero(),
            warnBalanceTooHigh: Balance::fromInt(10_000),
            codaFileType: CodaFormat::OnlyLines,
            codaGenerationPeriodType: CodaGenerationPeriod::EveryDay,
            codaGenerationStartWeekDay: CodaGenerationDayOfWeek::Monday,
            allowEntryAfterCoda: true,
            addPaymentReference: false,
        );

        // Act
        $result = $this->scrada->cashBook->update(
            companyId: $companyId,
            cashBookId: $cashBookId,
            data: $data,
        );

        // Assert
        $this->assertTrue($result);
    }
}
