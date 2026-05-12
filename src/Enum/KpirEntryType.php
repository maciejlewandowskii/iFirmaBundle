<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum KpirEntryType: string
{
    case Goods = 'TOW';
    case Services = 'USL';
    case GoodsAndServices = 'USL_TOW';
}
