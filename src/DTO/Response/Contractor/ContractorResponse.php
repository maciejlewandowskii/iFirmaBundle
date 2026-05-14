<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Contractor;

final readonly class ContractorResponse
{
    public function __construct(
        public string $name,
        public string $postalCode,
        public string $city,
        public string $identifier,
        public ?string $name2,
        public ?string $euPrefix,
        public ?string $taxId,
        public ?string $street,
        public ?string $country,
        public ?string $email,
        public ?string $phone,
        public bool $isNaturalPerson,
        public bool $eInvoiceConsent,
        public ?string $consentDate,
        public ?string $invoiceEmail,
        public bool $isSupplier,
        public bool $isRecipient,
        public bool $hasForeignAddress,
        public ?string $skype,
        public ?string $fax,
        public ?string $notes,
        public ?string $website,
        public ?string $bankName,
        public ?string $bankAccountNumber,
        public ?string $phone2,
        public ?string $mailingStreet,
        public ?string $mailingPostalCode,
        public ?string $mailingCountry,
        public ?string $mailingCity,
        public bool $isRelatedEntity,
    ) {
    }
}
