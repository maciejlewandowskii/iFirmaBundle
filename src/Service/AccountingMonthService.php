<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Request\AccountingMonth\ChangeAccountingMonthRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\AccountingMonth\AccountingMonthResponse;
use maciejlewandowskii\iFirmaApi\DTO\Response\ApiLimit\ApiLimitResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;

final class AccountingMonthService extends AbstractService
{
    public function get(): AccountingMonthResponse
    {
        $data = $this->client->get('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber);

        return new AccountingMonthResponse(
            month: $data->getInt('MiesiacKsiegowy'),
            year: $data->getInt('RokKsiegowy'),
        );
    }

    public function getApiLimit(): ApiLimitResponse
    {
        $data = $this->client->get('/abonent/limit.json', AuthKeyType::Subscriber);

        return new ApiLimitResponse(
            used: $data->getInt('LimitWykorzystany'),
            granted: $data->getInt('LimitPrzyznany'),
        );
    }

    /**
     * @throws ApiException
     */
    public function change(ChangeAccountingMonthRequest $request): AccountingMonthResponse
    {
        $this->validate($request);

        $this->client->put('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber, [
            'MiesiacKsiegowy' => $request->direction->value,
            'PrzeniesDaneZPoprzedniegoRoku' => $request->transferDataFromPreviousYear,
        ]);

        return $this->get();
    }
}
