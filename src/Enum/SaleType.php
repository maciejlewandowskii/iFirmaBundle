<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum SaleType: string
{
    case Taxable = 'OP';
    case Exempt = 'ZW';
    case TaxableAndExempt = 'OPIZW';
}
