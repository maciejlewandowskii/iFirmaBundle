<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum IdentityDocumentType: string
{
    case NationalId = 'DOWOD_OSOBISTY';
    case Passport = 'PASZPORT';
}
