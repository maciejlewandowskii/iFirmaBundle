<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum AdditionalNoteType: string
{
    case ContractNumber = 'NUMER_UMOWY';
    case OrderNumber = 'NUMER_ZAMOWIENIA';
    case TransportConditions = 'WARUNKI_TRANSPORTU';
}
