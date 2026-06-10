<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\Exception\ValidationException;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ContractorServiceValidationTest extends TestCase
{
    private ContractorService $service;

    protected function setUp(): void
    {
        $client = $this->createMock(iFirmaClientInterface::class);
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->service = new ContractorService($client, $validator);
    }

    public function testThrowsWhenNameIsBlank(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/name/i');

        $this->service->create(new CreateContractorRequest(
            name: '',
            postalCode: '00-001',
            city: 'Warszawa',
        ));
    }

    public function testThrowsWhenPostalCodeExceedsMaxLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/postalCode/i');

        $this->service->create(new CreateContractorRequest(
            name: 'Test Company',
            postalCode: str_repeat('X', 17),
            city: 'Warszawa',
        ));
    }

    public function testThrowsWhenEmailIsInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/email/i');

        $this->service->create(new CreateContractorRequest(
            name: 'Test Company',
            postalCode: '00-001',
            city: 'Warszawa',
            email: 'not-an-email',
        ));
    }

    public function testThrowsWhenCountryCodeIsNotTwoChars(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/countryCode/i');

        $this->service->create(new CreateContractorRequest(
            name: 'Test Company',
            postalCode: '00-001',
            city: 'Warszawa',
            countryCode: 'POL',
        ));
    }
}
