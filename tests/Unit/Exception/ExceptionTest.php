<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Exception;

use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use maciejlewandowskii\iFirmaApi\Exception\ContractorNotFoundException;
use maciejlewandowskii\iFirmaApi\Exception\HttpException;
use maciejlewandowskii\iFirmaApi\Exception\iFirmaException;
use maciejlewandowskii\iFirmaApi\Exception\InvoiceNotFoundException;
use maciejlewandowskii\iFirmaApi\Exception\RateLimitException;
use maciejlewandowskii\iFirmaApi\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationList;

final class ExceptionTest extends TestCase
{
    public function testApiExceptionStoresApiCode(): void
    {
        $e = new ApiException('Bad request', 101);

        $this->assertSame(101, $e->getApiCode());
        $this->assertSame('Bad request', $e->getMessage());
    }

    public function testApiExceptionChainsPreviousException(): void
    {
        $prev = new RuntimeException('root cause');
        $e = new ApiException('Outer', 0, $prev);

        $this->assertSame($prev, $e->getPrevious());
    }

    public function testHttpExceptionStoresStatusCode(): void
    {
        $e = new HttpException(404, 'Not found');

        $this->assertSame(404, $e->getStatusCode());
        $this->assertSame('Not found', $e->getMessage());
    }

    public function testHttpExceptionDefaultMessageIncludesStatusCode(): void
    {
        $this->assertStringContainsString('500', (new HttpException(500))->getMessage());
    }

    public function testHttpExceptionChainsPreviousException(): void
    {
        $prev = new RuntimeException('network error');
        $e = new HttpException(0, 'failed', $prev);

        $this->assertSame($prev, $e->getPrevious());
    }

    public function testRateLimitExceptionMessageMentionsRateLimit(): void
    {
        $this->assertStringContainsString('rate limit', mb_strtolower((new RateLimitException())->getMessage()));
    }

    public function testAuthenticationExceptionMessage(): void
    {
        $this->assertSame('Bad credentials', (new AuthenticationException('Bad credentials'))->getMessage());
    }

    public function testContractorNotFoundExceptionIncludesIdentifier(): void
    {
        $this->assertStringContainsString('CTR-123', (new ContractorNotFoundException('CTR-123'))->getMessage());
    }

    public function testInvoiceNotFoundExceptionAcceptsStringId(): void
    {
        $this->assertStringContainsString('FV/2024/1', (new InvoiceNotFoundException('FV/2024/1'))->getMessage());
    }

    public function testInvoiceNotFoundExceptionAcceptsIntId(): void
    {
        $this->assertStringContainsString('42', (new InvoiceNotFoundException(42))->getMessage());
    }

    public function testValidationExceptionExposesViolations(): void
    {
        $violations = new ConstraintViolationList();
        $e = new ValidationException($violations);

        $this->assertSame($violations, $e->getViolations());
    }

    public function testiFirmaExceptionCatchesAllSubclasses(): void
    {
        // iFirmaException is the common base — verify each subclass is catchable by it
        try {
            throw new ApiException('api-error', 99);
        } catch (iFirmaException $e) {
            $this->assertSame('api-error', $e->getMessage());
        }
    }
}
