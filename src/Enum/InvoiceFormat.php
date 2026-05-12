<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum InvoiceFormat: string
{
    case Pdf = 'pdf';
    case Json = 'json';
    case Xml = 'xml';
}
