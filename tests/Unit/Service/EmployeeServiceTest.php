<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\EmployeeAddressRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\EmployeeDeclarationsRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\SaveQuestionnaireRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\SendQuestionnaireRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Enum\Gender;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EmployeeServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private EmployeeService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new EmployeeService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    public function testSendQuestionnaireCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kwestionariusz/send.json', AuthKeyType::Subscriber)
            ->willReturn(new ApiResponse(['Kod' => 0]));

        $this->service->sendQuestionnaire(new SendQuestionnaireRequest(
            email: 'jan@kowalski.pl',
            firstName: 'Jan',
            lastName: 'Kowalski',
            isCooperatingPerson: false,
        ));
    }

    public function testSendQuestionnaireWithPhoneField(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kwestionariusz/send.json', AuthKeyType::Subscriber, $this->callback(
                static fn (array $body): bool => isset($body['Telefon']),
            ))
            ->willReturn(new ApiResponse(['Kod' => 0]));

        $this->service->sendQuestionnaire(new SendQuestionnaireRequest(
            email: 'test@example.com',
            firstName: 'Anna',
            lastName: 'Nowak',
            isCooperatingPerson: true,
            phone: '500600700',
        ));
    }

    public function testSaveQuestionnaireCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kwestionariusz.json', AuthKeyType::Subscriber)
            ->willReturn(new ApiResponse(['Kod' => 0]));

        $this->service->saveQuestionnaire(new SaveQuestionnaireRequest(
            isCooperatingPerson: false,
            email: 'jan@kowalski.pl',
            firstName: 'Jan',
            lastName: 'Kowalski',
            gender: Gender::Male,
            citizenship: 'Polskie',
            residenceInPoland: true,
            taxOfficeCode: '0202',
        ));
    }

    public function testSaveQuestionnaireWithDeclarations(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kwestionariusz.json', AuthKeyType::Subscriber, $this->callback(
                static fn (array $body): bool => isset($body['Oswiadczenia']),
            ))
            ->willReturn(new ApiResponse(['Kod' => 0]));

        $declarations = new EmployeeDeclarationsRequest(
            employedInOwnCompany: false,
            employedElsewhere: false,
            minimumWage: false,
            contractorSocialHealth: false,
            contractorHealthOnly: false,
            pensioner: false,
            retiree: false,
            student: true,
            businessActivity: false,
            subjectToKrus: false,
            noInsuranceTitle: false,
            unemployed: false,
            disabled: false,
            disabilityDegreeId: '0',
        );

        $this->service->saveQuestionnaire(new SaveQuestionnaireRequest(
            isCooperatingPerson: false,
            email: 'jan@kowalski.pl',
            firstName: 'Jan',
            lastName: 'Kowalski',
            gender: Gender::Male,
            citizenship: 'Polskie',
            residenceInPoland: true,
            taxOfficeCode: '0202',
            declarations: $declarations,
        ));
    }

    public function testGetQuestionnaireCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with('/kwestionariusz/find/ABC-123.json', AuthKeyType::Subscriber)
            ->willReturn('{"data":"..."}');

        $result = $this->service->getQuestionnaire('ABC-123');

        $this->assertStringContainsString('data', $result);
    }

    public function testGetQuestionnairePdfCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with('/kwestionariusz/find/ABC-123.pdf', AuthKeyType::Subscriber)
            ->willReturn('%PDF-1.4 fake');

        $result = $this->service->getQuestionnairePdf('ABC-123');

        $this->assertStringStartsWith('%PDF', $result);
    }

    public function testSaveQuestionnaireWithAddress(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kwestionariusz.json', AuthKeyType::Subscriber, $this->callback(
                static fn (array $body): bool => isset($body['AdresZameldowania']),
            ))
            ->willReturn(new ApiResponse(['Kod' => 0]));

        $address = new EmployeeAddressRequest(
            street: 'Grabiszyńska',
            houseNumber: '21',
            postalCode: '51-314',
            city: 'Wrocław',
            post: 'Wrocław',
            commune: 'm. wrocław',
            county: 'Wrocław',
            province: 'Dolnośląskie',
        );

        $this->service->saveQuestionnaire(new SaveQuestionnaireRequest(
            isCooperatingPerson: false,
            email: 'jan@kowalski.pl',
            firstName: 'Jan',
            lastName: 'Kowalski',
            gender: Gender::Male,
            citizenship: 'Polskie',
            residenceInPoland: true,
            taxOfficeCode: '0202',
            registeredAddress: $address,
        ));
    }

    public function testGetQuestionnaireUrlEncodesTicket(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with('/kwestionariusz/find/ABC%2F123.json')
            ->willReturn('{}');

        $this->service->getQuestionnaire('ABC/123');
    }
}
