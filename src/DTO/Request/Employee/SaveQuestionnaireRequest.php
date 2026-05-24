<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Employee;

use maciejlewandowskii\iFirmaApi\Enum\Gender;
use maciejlewandowskii\iFirmaApi\Enum\IdentityDocumentType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SaveQuestionnaireRequest
{
    public function __construct(
        #[SerializedName('OsobaWspolpracujaca')]
        public bool $isCooperatingPerson,

        #[SerializedName('Email')]
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(min: 1, max: 65)]
        public string $email,

        #[SerializedName('Imie')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 30)]
        public string $firstName,

        #[SerializedName('Nazwisko')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $lastName,

        #[SerializedName('Plec')]
        #[Assert\NotNull]
        public Gender $gender,

        #[SerializedName('Obywatelstwo')]
        #[Assert\NotBlank]
        public string $citizenship,

        #[SerializedName('MiejsceRezydencjiPolska')]
        public bool $residenceInPoland,

        #[SerializedName('UrzadSkarbowyKod')]
        #[Assert\NotBlank]
        public string $taxOfficeCode,

        #[SerializedName('Telefon')]
        #[Assert\Length(max: 16)]
        public ?string $phone = null,

        #[SerializedName('DrugieImie')]
        #[Assert\Length(max: 30)]
        public ?string $middleName = null,

        #[SerializedName('PESEL')]
        #[Assert\Length(exactly: 11)]
        public ?string $pesel = null,

        #[SerializedName('DataUrodzenia')]
        public ?string $birthDate = null,

        #[SerializedName('RodzajDokumentuTozsamosci')]
        public ?IdentityDocumentType $identityDocumentType = null,

        #[SerializedName('SeriaNumerDowoduTozsamosci')]
        #[Assert\Length(max: 20)]
        public ?string $identityDocumentNumber = null,

        #[SerializedName('KodNFZ')]
        public ?string $nfzCode = null,

        #[SerializedName('KontoBankowe')]
        #[Assert\Length(exactly: 26)]
        public ?string $bankAccount = null,

        #[SerializedName('NazwaBanku')]
        #[Assert\Length(max: 50)]
        public ?string $bankName = null,

        #[SerializedName('TypUkonczonejSzkolyId')]
        public ?int $schoolTypeId = null,

        #[SerializedName('StazPracy')]
        #[Assert\PositiveOrZero]
        public ?int $workExperienceYears = null,

        #[SerializedName('AdresZameldowania')]
        #[Assert\Valid]
        public ?EmployeeAddressRequest $registeredAddress = null,

        #[SerializedName('AdresZamieszkania')]
        #[Assert\Valid]
        public ?EmployeeAddressRequest $residenceAddress = null,

        #[SerializedName('AdresKorespondencyjny')]
        #[Assert\Valid]
        public ?EmployeeAddressRequest $correspondenceAddress = null,

        #[SerializedName('Oswiadczenia')]
        #[Assert\Valid]
        public ?EmployeeDeclarationsRequest $declarations = null,
    ) {
    }
}
