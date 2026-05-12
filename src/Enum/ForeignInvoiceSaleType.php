<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum ForeignInvoiceSaleType: string
{
    case MailOrder = 'WYSYLKOWA';
    case Domestic = 'KRAJOWA';
}
