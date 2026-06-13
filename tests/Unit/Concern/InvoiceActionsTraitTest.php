<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Concern;

use LogicException;
use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Concern\InvoiceActionsTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaInvoiceInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class InvoiceActionsTraitTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private iFirmaApi $api;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $this->api = new iFirmaApi(
            new InvoiceService($this->client, $validator),
            new ContractorService($this->client, $validator),
            new ExpenseService($this->client, $validator),
            new PaymentService($this->client, $validator),
            new OrderService($this->client, $validator),
            new AccountingMonthService($this->client, $validator),
            new VatDictionaryService($this->client, $validator),
            new EmployeeService($this->client, $validator),
        );
    }

    private function makeEntity(): IFirmaInvoiceInterface
    {
        $request = new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(name: 'Test', postalCode: '00-001', city: 'Warszawa'),
            positions: [new InvoicePositionRequest(
                name: 'Item',
                unit: 'szt',
                vatRateType: VatRateType::Percentage,
                quantity: 1.0,
                unitPrice: 100.0,
            )],
        );

        return new class($request) implements IFirmaInvoiceInterface {
            use IFirmaEntityTrait;
            use InvoiceActionsTrait;

            public function __construct(private readonly CreateInvoiceRequest $req)
            {
            }

            public function toCreateInvoiceRequest(): CreateInvoiceRequest
            {
                return $this->req;
            }
        };
    }

    public function testCreateOniFirmaSetsIdSyncedAtAndHash(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1/2024']));

        $entity = $this->makeEntity();
        $response = $entity->createOniFirma($this->api);

        $this->assertSame('FV/1/2024', $response->identifier);
        $this->assertSame('FV/1/2024', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
        $this->assertNotNull($entity->getIFirmaStateHash());
    }

    public function testIsSyncStaleWhenNotSynced(): void
    {
        $entity = $this->makeEntity();
        $this->assertTrue($entity->isSyncStale());
    }

    public function testIsSyncStaleReturnsFalseAfterSync(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1']));

        $entity = $this->makeEntity();
        $entity->createOniFirma($this->api);

        $this->assertFalse($entity->isSyncStale());
    }

    public function testIsSyncStaleReturnsTrueWhenHashDiffers(): void
    {
        $entity = $this->makeEntity();
        $entity->setIFirmaId('FV/1');
        $entity->setIFirmaStateHash('stale-hash');

        $this->assertTrue($entity->isSyncStale());
    }

    public function testGetPdfFromiFirma(): void
    {
        $this->client->method('getRaw')->willReturn('%PDF-1.4 content');

        $entity = $this->makeEntity();
        $entity->setIFirmaId('FV/1');

        $pdf = $entity->getPdfFromiFirma($this->api);

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function testGetPdfThrowsWhenNotSynced(): void
    {
        $this->expectException(LogicException::class);
        $this->makeEntity()->getPdfFromiFirma($this->api);
    }

    public function testSendToKsefOniFirma(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $entity = $this->makeEntity();
        $entity->setIFirmaId('FV/1');
        $entity->sendToKsefOniFirma($this->api);

        $this->addToAssertionCount(1);
    }

    public function testSendToKsefThrowsWhenNotSynced(): void
    {
        $this->expectException(LogicException::class);
        $this->makeEntity()->sendToKsefOniFirma($this->api);
    }

    public function testSendByEmailOniFirma(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $entity = $this->makeEntity();
        $entity->setIFirmaId('FV/1');
        $entity->sendByEmailOniFirma($this->api);

        $this->addToAssertionCount(1);
    }

    public function testSendByPostOniFirma(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $entity = $this->makeEntity();
        $entity->setIFirmaId('FV/1');
        $entity->sendByPostOniFirma($this->api);

        $this->addToAssertionCount(1);
    }
}
