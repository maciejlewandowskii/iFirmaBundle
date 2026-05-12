<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum KSeFDesignation: string
{
    case Number = 'NUMER';
    case Off = 'OFF';
    case NoFiscalDocument = 'BFK';
    case DirectImport = 'DI';
}
