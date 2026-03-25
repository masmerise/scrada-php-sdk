<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum ItemType: int
{
    case Product = 1;
    case Voucher = 2;
}
