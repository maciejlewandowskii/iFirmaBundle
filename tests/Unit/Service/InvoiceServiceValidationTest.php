<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\Exception\ValidationException;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class InvoiceServiceValidationTest extends TestCase
{
    private InvoiceService $service;

    protected function setUp(): void
    {
        $this->service = new InvoiceService(
            $this->createMock(iFirmaClientInterface::class),
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    private function validContractor(): InvoiceContractorRequest
    {
        return new InvoiceContractorRequest(
            name: 'Test Company',
            postalCode: '00-001',
            city: 'Warszawa',
        );
    }

    private function validPosition(): InvoicePositionRequest
    {
        return new InvoicePositionRequest(
            name: 'Test service',
            unit: 'szt',
            vatRateType: VatRateType::Percentage,
            quantity: 1.0,
            unitPrice: 100.0,
        );
    }

    /** @param list<InvoicePositionRequest>|null $positions */
    private function validRequest(
        ?string $issueDate = null,
        ?InvoiceContractorRequest $contractor = null,
        ?array $positions = null,
    ): CreateInvoiceRequest {
        return new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $issueDate ?? '2024-01-15',
            saleDate: '2024-01-15',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: $contractor ?? $this->validContractor(),
            positions: $positions ?? [$this->validPosition()],
        );
    }

    public function testThrowsValidationExceptionForEmptyContractorName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/name/i');

        $this->service->create($this->validRequest(
            contractor: new InvoiceContractorRequest(name: '', postalCode: '00-001', city: 'Warszawa'),
        ));
    }

    public function testThrowsValidationExceptionForEmptyPositions(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/positions/i');

        $this->service->create($this->validRequest(positions: []));
    }

    public function testThrowsValidationExceptionForInvalidDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/issueDate/i');

        $this->service->create($this->validRequest(issueDate: 'not-a-date'));
    }

    public function testThrowsValidationExceptionForNegativeQuantity(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/quantity/i');

        $this->service->create($this->validRequest(
            positions: [
                new InvoicePositionRequest(
                    name: 'Test service',
                    unit: 'szt',
                    vatRateType: VatRateType::Percentage,
                    quantity: -1.0,
                    unitPrice: 100.0,
                ),
            ],
        ));
    }
}
