<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum PaymentType: int
{
    case Payment = 1;
    case CashInOut = 2;
}
