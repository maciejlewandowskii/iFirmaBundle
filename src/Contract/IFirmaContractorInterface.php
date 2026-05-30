<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Contract;

use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Contractor\ContractorCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

interface IFirmaContractorInterface extends IFirmaEntityInterface
{
    public function toCreateContractorRequest(): CreateContractorRequest;

    public function toUpdateContractorRequest(): UpdateContractorRequest;

    public function isSyncStale(): bool;

    public function createOniFirma(iFirmaApi $api): ContractorCreatedResponse;

    public function updateOniFirma(iFirmaApi $api): ContractorCreatedResponse;
}
