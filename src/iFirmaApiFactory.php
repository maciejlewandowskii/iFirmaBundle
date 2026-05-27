<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi;

use maciejlewandowskii\iFirmaApi\Authentication\AuthenticatorInterface;
use maciejlewandowskii\iFirmaApi\Authentication\Credentials;
use maciejlewandowskii\iFirmaApi\Authentication\CredentialsHmacAuthenticator;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClient;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class iFirmaApiFactory
{
    public static function create(
        string $username,
        string $invoiceKeyHex,
        string $subscriberKeyHex,
        ?string $expenseKeyHex = null,
        ?string $accountKeyHex = null,
        ?LoggerInterface $logger = null,
        ?ValidatorInterface $validator = null,
        ?AuthenticatorInterface $authenticator = null,
        int $maxRetries = 3,
    ): iFirmaApi {
        $credentials = new Credentials(
            username: $username,
            invoiceKey: $invoiceKeyHex,
            subscriberKey: $subscriberKeyHex,
            expenseKey: $expenseKeyHex,
            accountKey: $accountKeyHex,
        );

        $resolvedAuthenticator = $authenticator ?? new CredentialsHmacAuthenticator($credentials);
        $resolvedLogger = $logger ?? new NullLogger();
        $resolvedValidator = $validator ?? Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $baseHttpClient = HttpClient::create([
            'timeout' => 10.0,
            'max_duration' => 30.0,
        ]);

        $httpClient = new RetryableHttpClient(
            client: $baseHttpClient,
            strategy: new GenericRetryStrategy(
                statusCodes: [500, 502, 503, 504],
                delayMs: 500,
                multiplier: 2.0,
                maxDelayMs: 5_000,
                jitter: 0.1,
            ),
            maxRetries: $maxRetries,
            logger: $resolvedLogger,
        );

        $client = new iFirmaClient(
            httpClient: $httpClient,
            authenticator: $resolvedAuthenticator,
            credentials: $credentials,
            logger: $resolvedLogger,
        );

        return new iFirmaApi(
            invoiceService: new InvoiceService($client, $resolvedValidator),
            contractorService: new ContractorService($client, $resolvedValidator),
            expenseService: new ExpenseService($client, $resolvedValidator),
            paymentService: new PaymentService($client, $resolvedValidator),
            orderService: new OrderService($client, $resolvedValidator),
            accountingMonthService: new AccountingMonthService($client, $resolvedValidator),
            vatDictionaryService: new VatDictionaryService($client, $resolvedValidator),
            employeeService: new EmployeeService($client, $resolvedValidator),
        );
    }
}
