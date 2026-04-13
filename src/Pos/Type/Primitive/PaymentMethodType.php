<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum PaymentMethodType: int
{
    case Other = 1;
    case Cash = 2;
    case WireTransfer = 4;
    case DebitCard = 5;
    case CreditCard = 6;
    case GiftCard = 7;
}
