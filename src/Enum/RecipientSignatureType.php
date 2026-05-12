<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum RecipientSignatureType: string
{
    case PersonAuthorized = 'OUP';
    case AuthorizedPerson = 'UPO';
    case WithoutRecipient = 'BPO';
    case WithoutSignatures = 'BWO';
}
