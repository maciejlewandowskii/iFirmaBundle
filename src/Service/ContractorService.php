<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use Generator;
use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Contractor\ContractorCreatedResponse;
use maciejlewandowskii\iFirmaApi\DTO\Response\Contractor\ContractorResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\ContractorNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

use function sprintf;

final class ContractorService extends AbstractService
{
    /**
     * @throws ExceptionInterface
     */
    public function create(CreateContractorRequest $request): ContractorCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/kontrahenci.json', AuthKeyType::Invoice, $this->toArray($request));

        return new ContractorCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getString('Wynik'),
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function update(string $identifier, UpdateContractorRequest $request): ContractorCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->put(
            sprintf('/kontrahenci/%s.json', urlencode($identifier)),
            AuthKeyType::Invoice,
            $this->toArray($request),
        );

        return new ContractorCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getString('Wynik'),
        );
    }

    public function get(string $identifier): ContractorResponse
    {
        try {
            $data = $this->client->get(
                sprintf('/kontrahenci/%s.json', urlencode($identifier)),
                AuthKeyType::Invoice,
            );
        } catch (ApiException) {
            throw new ContractorNotFoundException($identifier);
        }

        $wynik = $data->getFirstResponse('Wynik');

        if (!$wynik instanceof ApiResponse) {
            throw new ContractorNotFoundException($identifier);
        }

        return $this->hydrate($wynik);
    }

    /**
     * @return Generator<int, ContractorResponse>
     */
    public function search(string $phrase): Generator
    {
        $data = $this->client->get(
            sprintf('/kontrahenci/%s.json', rawurlencode($phrase)),
            AuthKeyType::Invoice,
        );

        foreach ($data->getResponseList('Wynik') as $item) {
            yield $this->hydrate($item);
        }
    }

    private function hydrate(ApiResponse $data): ContractorResponse
    {
        return new ContractorResponse(
            name: $data->getString('Nazwa'),
            postalCode: $data->getString('KodPocztowy'),
            city: $data->getString('Miejscowosc'),
            identifier: $data->getString('Identyfikator'),
            name2: $data->getNullableString('Nazwa2'),
            euPrefix: $data->getNullableString('PrefiksUE'),
            taxId: $data->getNullableString('NIP'),
            street: $data->getNullableString('Ulica'),
            country: $data->getNullableString('Kraj'),
            email: $data->getNullableString('Email'),
            phone: $data->getNullableString('Telefon'),
            isNaturalPerson: $data->getBool('OsobaFizyczna'),
            eInvoiceConsent: $data->getBool('ZgodaNaEfaktury'),
            consentDate: $data->getNullableString('DataUdzieleniaZgody'),
            invoiceEmail: $data->getNullableString('EmailDlaFaktury'),
            isSupplier: $data->getBool('JestDostawca'),
            isRecipient: $data->getBool('JestOdbiorca'),
            hasForeignAddress: $data->getBool('AdresZagraniczny'),
            skype: $data->getNullableString('Skype'),
            fax: $data->getNullableString('Faks'),
            notes: $data->getNullableString('Uwagi'),
            website: $data->getNullableString('Www'),
            bankName: $data->getNullableString('NazwaBanku'),
            bankAccountNumber: $data->getNullableString('NumerKonta'),
            phone2: $data->getNullableString('DrugiTelefon'),
            mailingStreet: $data->getNullableString('AdresKorespondencyjnyUlica'),
            mailingPostalCode: $data->getNullableString('AdresKorespondencyjnyKodPocztowy'),
            mailingCountry: $data->getNullableString('AdresKorespondencyjnyKraj'),
            mailingCity: $data->getNullableString('AdresKorespondencyjnyMiejscowosc'),
            isRelatedEntity: $data->getBool('PodmiotPowiazany'),
        );
    }
}
