<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Authentication;

use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;

/**
 * Selects the correct HMAC key from Credentials based on the keyName in each request.
 * iFirma uses a separate hex key per operation type (faktura, abonent, wydatek, rachunek).
 */
final readonly class CredentialsHmacAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        private Credentials $credentials,
    ) {
    }

    public function buildAuthorizationHeader(string $url, string $username, string $keyName, string $requestBody): string
    {
        $keyType = AuthKeyType::from($keyName);
        $hexKey = $this->credentials->getKeyForType($keyType);

        return (new HmacKeyAuthenticator($hexKey))->buildAuthorizationHeader($url, $username, $keyName, $requestBody);
    }
}
