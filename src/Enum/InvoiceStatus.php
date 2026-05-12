<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum InvoiceStatus: string
{
    case Overdue = 'przeterminowane';
    case Unpaid = 'nieoplacone';
    case PartiallyPaid = 'oplaconeCzesciowo';
    case Paid = 'oplacone';
}
