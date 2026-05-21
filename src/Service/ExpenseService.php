<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Expense\ExpenseCreatedResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class ExpenseService extends AbstractService
{
    /**
     * @throws ExceptionInterface
     */
    public function createVatPurchase(CreateVatPurchaseRequest $request): ExpenseCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/zakuptowaruvat.json', AuthKeyType::Expense, $this->toArray($request));

        return new ExpenseCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function createActivityCost(CreateVatPurchaseRequest $request): ExpenseCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/kosztdzialalnoscivat.json', AuthKeyType::Expense, $this->toArray($request));

        return new ExpenseCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function createOtherCost(CreateOtherCostRequest $request): ExpenseCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/kosztdzialalnosci.json', AuthKeyType::Expense, $this->toArray($request));

        return new ExpenseCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function createPhoneInternetCost(CreateVatPurchaseRequest $request): ExpenseCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/oplatatelefon.json', AuthKeyType::Expense, $this->toArray($request));

        return new ExpenseCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }
}
