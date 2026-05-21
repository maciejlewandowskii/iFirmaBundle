<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Expense;

use maciejlewandowskii\iFirmaApi\Enum\ExpenseDocumentType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOtherCostRequest
{
    public function __construct(
        #[SerializedName('RodzajDokumentu')]
        #[Assert\NotNull]
        public ExpenseDocumentType $documentType,

        #[SerializedName('NumerDokumentu')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $documentNumber,

        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        #[SerializedName('TerminPlatnosci')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $paymentDeadline,

        #[SerializedName('NazwaWydatku')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $expenseName,

        #[SerializedName('Kwota')]
        #[Assert\NotNull]
        #[Assert\Positive]
        #[Assert\LessThan(100_000_000)]
        public float $amount,

        #[SerializedName('Kontrahent')]
        #[Assert\NotNull]
        #[Assert\Valid]
        public ExpenseContractorRequest $contractor,

        #[SerializedName('IdentyfikatorKontrahenta')]
        #[Assert\Length(max: 15)]
        public ?string $contractorIdentifier = null,

        #[SerializedName('PrefiksUEKontrahenta')]
        #[Assert\Length(max: 2)]
        public ?string $contractorEuPrefix = null,

        #[SerializedName('DataWplywu')]
        #[Assert\Date]
        public ?string $receiptDate = null,
    ) {
    }
}
