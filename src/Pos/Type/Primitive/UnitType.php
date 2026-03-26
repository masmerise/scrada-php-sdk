<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum UnitType: int
{
    case OneOrUnit = 1;
    case Piece = 2;
    case Pallet = 3;
    case Container20ft = 4;
    case Container40ft = 5;
    case Second = 100;
    case Minute = 101;
    case Hour = 102;
    case Day = 103;
    case Month = 104;
    case Year = 105;
    case Week = 106;
    case Milligram = 200;
    case Gram = 201;
    case Kilogram = 202;
    case Ton = 203;
    case Meter = 300;
    case Kilometer = 301;
    case Liter = 400;
    case Milliliter = 401;
    case Hectare = 500;
}
