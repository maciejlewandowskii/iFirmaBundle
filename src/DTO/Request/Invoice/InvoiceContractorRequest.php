<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class InvoiceContractorRequest
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

        #[SerializedName('Identyfikator')]
        #[Assert\Length(max: 15)]
        public ?string $identifier = null,

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

        #[SerializedName('Email')]
        #[Assert\Email]
        #[Assert\Length(max: 65)]
        public ?string $email = null,

        #[SerializedName('Telefon')]
        #[Assert\Length(max: 32)]
        public ?string $phone = null,

        #[SerializedName('OsobaFizyczna')]
        public ?bool $isNaturalPerson = null,

        #[SerializedName('JestOdbiorca')]
        public ?bool $isRecipient = null,

        #[SerializedName('JestDostawca')]
        public ?bool $isSupplier = null,

        #[SerializedName('PodmiotPowiazany')]
        public ?bool $isRelatedEntity = null,
    ) {
    }
}
