<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum ProFormaInvoiceType: string
{
    case Sale = 'SPRZ';
    case Construction = 'BUD';
    case Advance = 'ZAL';
}
