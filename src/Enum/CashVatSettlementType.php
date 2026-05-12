<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum CashVatSettlementType: string
{
    case CashAccounting = 'MET_KASOWA';
    case Consignment = 'KOMIS';
    case CustomsDecision = 'POST_SAD';
    case ExemptServices = 'USLUGI_ZW';
}
