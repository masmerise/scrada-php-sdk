<?php declare(strict_types=1);

namespace Scrada\CashBook\Type\Primitive;

enum CodaGenerationPeriod: int
{
    case EveryDay = 1;
    case EveryWeek = 2;
    case EveryMonth = 3;
}
