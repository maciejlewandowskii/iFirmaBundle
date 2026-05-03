<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Authentication;

use LogicException;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;

final readonly class Credentials
{
    public function __construct(
        private string $username,
        private string $invoiceKey,
        private string $subscriberKey,
        private ?string $expenseKey = null,
        private ?string $accountKey = null,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getKeyForType(AuthKeyType $type): string
    {
        return match ($type) {
            AuthKeyType::Invoice => $this->invoiceKey,
            AuthKeyType::Subscriber => $this->subscriberKey,
            AuthKeyType::Expense => $this->expenseKey ?? throw new LogicException('Expense key not configured'),
            AuthKeyType::Account => $this->accountKey ?? throw new LogicException('Account key not configured'),
        };
    }
}
