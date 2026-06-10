<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Payment\RegisterPaymentRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ValidationException;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class PaymentServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private PaymentService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new PaymentService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    public function testRegisterPostsToCorrectPathWithSlashesReplaced(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with($this->stringContains('FV_2024_1'), AuthKeyType::Invoice)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'Registered']));

        $result = $this->service->register(new RegisterPaymentRequest(
            invoiceType: 'fakturakraj',
            invoiceNumber: 'FV/2024/1',
            amount: 123.0,
        ));

        $this->assertSame(0, $result->code);
        $this->assertNull($result->id);
    }

    public function testRegisterIncludesOnlyNonNullFieldsInBody(): void
    {
        $capturedBody = [];

        $this->client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->anything(), $this->callback(
                static function (array $body) use (&$capturedBody): true {
                    $capturedBody = $body;

                    return true;
                },
            ))
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $this->service->register(new RegisterPaymentRequest(
            invoiceType: 'fakturakraj',
            invoiceNumber: 'FV/2024/1',
            amount: 50.0,
            date: '2024-03-01',
        ));

        $this->assertArrayHasKey('Kwota', $capturedBody);
        $this->assertArrayHasKey('Data', $capturedBody);
        $this->assertArrayNotHasKey('KwotaPln', $capturedBody);
        $this->assertArrayNotHasKey('Kurs', $capturedBody);
    }

    public function testRegisterWithAllOptionalFieldsIncludesThemInBody(): void
    {
        $capturedBody = [];

        $this->client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->anything(), $this->callback(
                static function (array $body) use (&$capturedBody): true {
                    $capturedBody = $body;

                    return true;
                },
            ))
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => '99']));

        $result = $this->service->register(new RegisterPaymentRequest(
            invoiceType: 'fakturakraj',
            invoiceNumber: 'FV/2024/1',
            amount: 100.0,
            date: '2024-03-01',
            amountPln: 100.0,
            exchangeRate: 1.0,
        ));

        $this->assertArrayHasKey('KwotaPln', $capturedBody);
        $this->assertArrayHasKey('Kurs', $capturedBody);
        $this->assertSame('99', $result->id);
    }

    public function testRegisterThrowsValidationExceptionForBlankInvoiceType(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->register(new RegisterPaymentRequest(
            invoiceType: '',
            invoiceNumber: 'FV/2024/1',
            amount: 100.0,
        ));
    }
}
