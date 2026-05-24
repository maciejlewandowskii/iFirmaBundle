<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Employee;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class EmployeeAddressRequest
{
    public function __construct(
        #[SerializedName('Ulica')]
        #[Assert\NotBlank]
        public string $street,

        #[SerializedName('NumerDomu')]
        #[Assert\NotBlank]
        public string $houseNumber,

        #[SerializedName('KodPocztowy')]
        #[Assert\NotBlank]
        public string $postalCode,

        #[SerializedName('Miejscowosc')]
        #[Assert\NotBlank]
        public string $city,

        #[SerializedName('Poczta')]
        #[Assert\NotBlank]
        public string $post,

        #[SerializedName('Gmina')]
        #[Assert\NotBlank]
        public string $commune,

        #[SerializedName('Powiat')]
        #[Assert\NotBlank]
        public string $county,

        #[SerializedName('Wojewodztwo')]
        #[Assert\NotBlank]
        public string $province,

        #[SerializedName('AdresZagraniczny')]
        public bool $isForeignAddress = false,

        #[SerializedName('NumerLokalu')]
        public ?string $apartmentNumber = null,

        #[SerializedName('Kraj')]
        public ?string $country = null,
    ) {
    }
}
