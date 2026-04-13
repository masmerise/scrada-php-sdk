<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum VatType: int
{
    case Standard = 1;
    case ZeroRate = 2;
    case ExemptFromTax = 3;
    case IcdServicesB2B = 4;
    case IcdGoods = 5;
    case IcdManufacturingCost = 6;
    case IcdAssembly = 7;
    case IcdDistance = 8;
    case IcdServices = 9;
    case IcdTriangle = 10;
    case ExportNonEu = 20;
    case IndirectExport = 21;
    case ExportViaEu = 22;
    case ReverseCharge = 50;
    case FinancialDiscount = 51;
    case Article44 = 52;
    case StandardExchange = 53;
    case Margin = 54;
    case OssGoods = 70;
    case OssServices = 71;
    case OssImport = 72;
    case OutsideScopeOfTax = 99;
}
