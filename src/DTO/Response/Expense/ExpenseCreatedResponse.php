<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Expense;

final readonly class ExpenseCreatedResponse
{
    public function __construct(
        public int $code,
        public string $message,
        public ?string $id = null,
    ) {
    }
}
