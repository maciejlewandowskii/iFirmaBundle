<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum CorrectiveReasonType: string
{
    case MandatoryDiscount = 'OBOW_RABAT';
    case ReturnOfGoods = 'ZWR_SPRZ_TOW';
    case ReturnOfBuyerAmount = 'ZWR_NAB_KWOT';
    case ReturnOfAdvance = 'ZWR_NAB_ZAL';
    case PriceIncrease = 'PODW_CENY';
    case Mistake = 'POMYLKI';
}
