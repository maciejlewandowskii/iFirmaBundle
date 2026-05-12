<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum VatRateType: string
{
    case Percentage = 'PRC';
    case Exempt = 'ZW';
}
