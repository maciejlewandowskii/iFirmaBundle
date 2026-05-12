<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum PaymentMethod: string
{
    case Cash = 'GTK';
    case CollectOnDelivery = 'POB';
    case Transfer = 'PRZ';
    case Card = 'KAR';
    case Prepayment = 'PZA';
    case Cheque = 'CZK';
    case Compensation = 'KOM';
    case Barter = 'BAR';
    case Subsidy = 'DOT';
    case PayPal = 'PAL';
    case Allegro = 'ALG';
    case Przelewy24 = 'P24';
    case Tpay = 'TPA';
    case Electronic = 'ELE';
}
