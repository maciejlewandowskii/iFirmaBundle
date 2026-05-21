<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Response\VatDictionary\EuVatRateItemResponse;
use maciejlewandowskii\iFirmaApi\DTO\Response\VatDictionary\EuVatRatesResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;

use function sprintf;

final class VatDictionaryService extends AbstractService
{
    public function getEuVatRates(string $countryCode, ?string $date = null): EuVatRatesResponse
    {
        $path = sprintf('/slownik/stawki_vat/%s.json', urlencode($countryCode));
        $params = null !== $date ? ['data' => $date] : [];

        $data = $this->client->get($path, AuthKeyType::Invoice, $params);

        $rates = [];

        foreach ($data->getResponseList('StawkiVat') as $item) {
            $rates[] = new EuVatRateItemResponse(
                type: $item->getString('Rodzaj'),
                value: $item->getFloat('Wartosc'),
            );
        }

        return new EuVatRatesResponse(
            countryCode: $data->getString('KodKraju'),
            countryName: $data->getString('NazwaKraju'),
            rates: $rates,
        );
    }
}
