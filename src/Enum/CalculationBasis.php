<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum CalculationBasis: string
{
    case Net = 'NET';
    case Gross = 'BRT';
}
