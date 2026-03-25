<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum PaymentMethodType: int
{
    case Other = 1;
    case Cash = 2;
    case Cheque = 3;
    case DebitCard = 4;
    case Bancontact = 5;
    case CreditCard = 6;
    case GiftCard = 7;
    case EGiftCard = 8;
}
