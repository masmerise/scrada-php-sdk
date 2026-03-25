<?php declare(strict_types=1);

namespace Scrada\Pos\Type\Primitive;

enum UnitType: int
{
    case Piece = 1;
    case Pair = 2;
    case Box = 3;
    case Container = 99;
    case SquareMetre = 100;
    case SquareFoot = 200;
    case RunningMetre = 300;
    case KilometreWatt = 400;
    case Hour = 500;
    case Day = 600;
    case Month = 700;
    case Kg = 800;
    case Gram = 801;
    case Milligram = 802;
    case Tonne = 803;
    case Ounce = 804;
    case Pound = 805;
    case Litre = 900;
    case Millilitre = 901;
    case Centilitre = 902;
    case Kilolitre = 903;
    case Kilometre = 1000;
    case Hectare = 1100;
}
