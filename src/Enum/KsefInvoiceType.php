<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum KsefInvoiceType: string
{
    case Domestic = 'fakturakraj';
    case DomesticCorrective = 'fakturakraj/korekta';
    case MailOrder = 'fakturawysylka';
    case Advance = 'fakturazaliczka';
    case Final = 'fakturakoncowa';
    case AdvanceForeignCurrency = 'fakturazaliczkowaluta';
    case FinalForeignCurrency = 'fakturakoncowawaluta';
    case ExportGoods = 'fakturaeksporttowarow';
    case Wdt = 'fakturawdt';
    case ExportServices = 'fakturaeksportuslug';
    case EuServices = 'fakturaeksportuslugue';
    case ForeignCurrency = 'fakturawaluta';
    case Construction = 'fakturabudowa';
    case FromReceipt = 'fakturaparagon';
    case Equipment = 'fakturawyposazenie';
    case FixedAsset = 'fakturasrodektrwaly';
    case CashVat = 'fakturametodakasowa';
    case SpecialObligation = 'fakturaszczegolnyobowiazek';
    case Oss = 'fakturaoss';
}
