<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum VatType: int
{
    case Standard = 1;
    case ZeroRate = 2;
    case ExemptFromTax = 3;
    case IclServicesB2B = 4;
    case IclGoods = 5;
    case Montage = 6;
    case Distance = 7;
    case IclServicesB2C = 8;
    case Triangle = 10;
    case Export = 11;
    case ExportVia = 13;
    case IndirectExport = 21;
    case ExportGoodsEu = 22;
    case OssImport = 72;
    case OssServices = 75;
    case OssGoods = 76;
    case ReverseCharge = 80;
    case FinancialDiscount = 81;
    case Article44 = 82;
    case Margin = 84;
    case StandardExchange = 88;
}
