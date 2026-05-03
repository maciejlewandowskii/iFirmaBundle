<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Authentication;

interface AuthenticatorInterface
{
    public function buildAuthorizationHeader(string $url, string $username, string $keyName, string $requestBody): string;
}
