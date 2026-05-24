<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Employee;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class EmployeeDeclarationsRequest
{
    public function __construct(
        #[SerializedName('ZatrudnienieRodzimyZaklad')]
        public bool $employedInOwnCompany,

        #[SerializedName('ZatrudnienieInnyZaklad')]
        public bool $employedElsewhere,

        #[SerializedName('MinimalneWynagrodzenie')]
        public bool $minimumWage,

        #[SerializedName('ZleceniobiorcaSpoleczneZdrowotne')]
        public bool $contractorSocialHealth,

        #[SerializedName('ZleceniobiorcaTylkoZdrowotne')]
        public bool $contractorHealthOnly,

        #[SerializedName('Rencista')]
        public bool $pensioner,

        #[SerializedName('Emeryt')]
        public bool $retiree,

        #[SerializedName('Student')]
        public bool $student,

        #[SerializedName('DzialalnoscGospodarcza')]
        public bool $businessActivity,

        #[SerializedName('PodlegaKRUS')]
        public bool $subjectToKrus,

        #[SerializedName('BrakTytuluUbezpieczenia')]
        public bool $noInsuranceTitle,

        #[SerializedName('Bezrobotny')]
        public bool $unemployed,

        #[SerializedName('Niepelnosprawny')]
        public bool $disabled,

        #[SerializedName('StopienNiepelnosprawnosciId')]
        public string $disabilityDegreeId,
    ) {
    }
}
