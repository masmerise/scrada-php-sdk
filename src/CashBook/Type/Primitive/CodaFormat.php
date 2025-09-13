<?php declare(strict_types=1);

namespace Scrada\CashBook\Type\Primitive;

enum CodaFormat: int
{
    case OnlyLines = 1;
    case LinesAndPaymentMethods = 2;
}
