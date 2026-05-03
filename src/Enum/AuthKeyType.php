<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum AuthKeyType: string
{
    case Subscriber = 'abonent';
    case Invoice = 'faktura';
    case Account = 'rachunek';
    case Expense = 'wydatek';
}
