<?php declare(strict_types=1);

namespace Scrada\Company\Type\Primitive;

enum InvoiceInfo: int
{
    case CompanyAddress = 1;
    case InvoiceAddress = 2;
    case OtherCompanyInformation = 3;
}
