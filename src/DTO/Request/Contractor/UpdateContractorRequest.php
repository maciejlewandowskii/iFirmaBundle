<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Contractor;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Full contractor data is required on update; any omitted fields will be deleted from the record.
 */
final readonly class UpdateContractorRequest
{
    public function __construct(
        #[SerializedName('Nazwa')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 150)]
        public string $name,

        #[SerializedName('KodPocztowy')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 16)]
        public string $postalCode,

        #[SerializedName('Miejscowosc')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 65)]
        public string $city,

        #[SerializedName('Nazwa2')]
        #[Assert\Length(max: 150)]
        public ?string $name2 = null,

        #[SerializedName('PrefiksUE')]
        #[Assert\Length(max: 2)]
        public ?string $euPrefix = null,

        #[SerializedName('NIP')]
        #[Assert\Length(max: 13)]
        public ?string $taxId = null,

        #[SerializedName('Ulica')]
        #[Assert\Length(max: 65)]
        public ?string $street = null,

        #[SerializedName('Kraj')]
        #[Assert\Length(max: 70)]
        public ?string $country = null,

        #[SerializedName('KodKraju')]
        #[Assert\Length(exactly: 2)]
        public ?string $countryCode = null,

        #[SerializedName('AdresKorespondencyjnyUlica')]
        #[Assert\Length(max: 65)]
        public ?string $mailingStreet = null,

        #[SerializedName('AdresKorespondencyjnyKodPocztowy')]
        #[Assert\Length(min: 1, max: 16)]
        public ?string $mailingPostalCode = null,

        #[SerializedName('AdresKorespondencyjnyKraj')]
        #[Assert\Length(max: 70)]
        public ?string $mailingCountry = null,

        #[SerializedName('AdresKorespondencyjnyMiejscowosc')]
        #[Assert\Length(min: 1, max: 65)]
        public ?string $mailingCity = null,

        #[SerializedName('Email')]
        #[Assert\Email]
        #[Assert\Length(max: 65)]
        public ?string $email = null,

        #[SerializedName('EmailDlaFaktury')]
        #[Assert\Email]
        #[Assert\Length(max: 65)]
        public ?string $invoiceEmail = null,

        #[SerializedName('Telefon')]
        #[Assert\Length(max: 32)]
        public ?string $phone = null,

        #[SerializedName('DrugiTelefon')]
        #[Assert\Length(max: 32)]
        public ?string $phone2 = null,

        #[SerializedName('Faks')]
        #[Assert\Length(max: 32)]
        public ?string $fax = null,

        #[SerializedName('Skype')]
        #[Assert\Length(max: 32)]
        public ?string $skype = null,

        #[SerializedName('Www')]
        #[Assert\Length(max: 32)]
        public ?string $website = null,

        #[SerializedName('NazwaBanku')]
        #[Assert\Length(max: 32)]
        public ?string $bankName = null,

        #[SerializedName('NumerKonta')]
        #[Assert\Length(max: 28)]
        public ?string $bankAccountNumber = null,

        #[SerializedName('OsobaFizyczna')]
        public ?bool $isNaturalPerson = null,

        #[SerializedName('PodmiotPowiazany')]
        public ?bool $isRelatedEntity = null,

        #[SerializedName('ZgodaNaEfaktury')]
        public ?bool $eInvoiceConsent = null,

        #[SerializedName('DataUdzieleniaZgody')]
        #[Assert\Date]
        public ?string $consentDate = null,

        #[SerializedName('JestOdbiorca')]
        public ?bool $isRecipient = null,

        #[SerializedName('JestDostawca')]
        public ?bool $isSupplier = null,

        #[SerializedName('Uwagi')]
        public ?string $notes = null,
    ) {
    }
}
