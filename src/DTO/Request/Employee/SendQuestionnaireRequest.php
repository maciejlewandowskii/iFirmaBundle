<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Employee;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SendQuestionnaireRequest
{
    public function __construct(
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

        #[SerializedName('OsobaWspolpracujaca')]
        public bool $isCooperatingPerson,

        #[SerializedName('Telefon')]
        #[Assert\Length(max: 16)]
        public ?string $phone = null,
    ) {
    }
}
