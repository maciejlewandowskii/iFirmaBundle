<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\DTO;

use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\AdditionalNoteRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\JpkProceduresRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Order\OrderAddressRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Order\OrderPickupPointRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Order\OrderTrackingRequest;
use maciejlewandowskii\iFirmaApi\Enum\AdditionalNoteType;
use PHPUnit\Framework\TestCase;

final class RequestDtoTest extends TestCase
{
    public function testAdditionalNoteRequestStoresFields(): void
    {
        $dto = new AdditionalNoteRequest(
            type: AdditionalNoteType::ContractNumber,
            info: 'Express shipping',
            date: '2024-03-15',
        );

        $this->assertSame(AdditionalNoteType::ContractNumber, $dto->type);
        $this->assertSame('Express shipping', $dto->info);
        $this->assertSame('2024-03-15', $dto->date);
    }

    public function testAdditionalNoteRequestWithoutOptionalDate(): void
    {
        $dto = new AdditionalNoteRequest(
            type: AdditionalNoteType::ContractNumber,
            info: 'Note without date',
        );

        $this->assertNull($dto->date);
    }

    public function testJpkProceduresRequestAllFieldsNullByDefault(): void
    {
        $dto = new JpkProceduresRequest();

        $this->assertNull($dto->tp);
        $this->assertNull($dto->ied);
        $this->assertNull($dto->ee);
        $this->assertNull($dto->bspv);
        $this->assertNull($dto->bspvDostawa);
        $this->assertNull($dto->bmpvProwizja);
    }

    public function testJpkProceduresRequestWithSomeFlags(): void
    {
        $dto = new JpkProceduresRequest(tp: true, ee: false);

        $this->assertTrue($dto->tp);
        $this->assertFalse($dto->ee);
        $this->assertNull($dto->ied);
    }

    public function testOrderAddressRequestStoresAllFields(): void
    {
        $dto = new OrderAddressRequest(
            firstName: 'Jan',
            lastName: 'Kowalski',
            city: 'Warszawa',
            postcode: '00-001',
            country: 'PL',
            email: 'jan@example.com',
            nip: '5252344078',
        );

        $this->assertSame('Jan', $dto->firstName);
        $this->assertSame('Warszawa', $dto->city);
        $this->assertSame('jan@example.com', $dto->email);
    }

    public function testOrderAddressRequestAllOptional(): void
    {
        $dto = new OrderAddressRequest();

        $this->assertNull($dto->firstName);
        $this->assertNull($dto->email);
        $this->assertNull($dto->nip);
    }

    public function testOrderPickupPointRequestStoresFields(): void
    {
        $dto = new OrderPickupPointRequest(
            method: 'InPost',
            externalId: 'WAW001',
            name: 'Paczkomat WAW001',
            city: 'Warszawa',
            countryCode: 'PL',
        );

        $this->assertSame('InPost', $dto->method);
        $this->assertSame('WAW001', $dto->externalId);
    }

    public function testOrderPickupPointRequestAllOptional(): void
    {
        $dto = new OrderPickupPointRequest();

        $this->assertNull($dto->method);
        $this->assertNull($dto->city);
    }

    public function testOrderTrackingRequestStoresNumber(): void
    {
        $dto = new OrderTrackingRequest(number: '123456789012345678');

        $this->assertSame('123456789012345678', $dto->number);
    }
}
