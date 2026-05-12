<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum AccountingMonthDirection: string
{
    case Next = 'NAST';
    case Previous = 'POPRZ';
}
