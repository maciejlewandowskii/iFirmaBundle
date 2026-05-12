<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum ExpenseDocumentType: string
{
    case Receipt = 'RACH';
    case FiscalReceipt = 'PAR';
    case DeliveryDocument = 'DOW_DOST';
    case Contract = 'UM';
    case PaymentConfirmation = 'DOW_OPL';
    case AccountingNote = 'NOTA_KS';
    case ReceptionConfirmation = 'POKW_ODB';
    case Ticket = 'BIL';
}
