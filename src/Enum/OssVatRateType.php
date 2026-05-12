<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum OssVatRateType: string
{
    case Standard = 'POD';
    case Reduced1 = 'PR1';
    case Reduced2 = 'PR2';
    case Reduced3 = 'PR3';
    case Exempt = 'ZW';
}
