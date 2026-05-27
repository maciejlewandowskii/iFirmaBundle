<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi;

use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;

final readonly class iFirmaApi
{
    public function __construct(
        public InvoiceService $invoiceService,
        public ContractorService $contractorService,
        public ExpenseService $expenseService,
        public PaymentService $paymentService,
        public OrderService $orderService,
        public AccountingMonthService $accountingMonthService,
        public VatDictionaryService $vatDictionaryService,
        public EmployeeService $employeeService,
    ) {
    }
}
