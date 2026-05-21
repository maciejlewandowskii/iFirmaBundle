<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Request\Payment\RegisterPaymentRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Payment\PaymentRegisteredResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;

use function sprintf;

final class PaymentService extends AbstractService
{
    public function register(RegisterPaymentRequest $request): PaymentRegisteredResponse
    {
        $this->validate($request);

        $invoiceNumber = str_replace('/', '_', $request->invoiceNumber);
        $path = sprintf('/faktury/wplaty/%s/%s.json', urlencode($request->invoiceType), urlencode($invoiceNumber));

        $body = array_filter([
            'Kwota' => $request->amount,
            'Data' => $request->date,
            'KwotaPln' => $request->amountPln,
            'Kurs' => $request->exchangeRate,
        ], static fn (string|float|null $v): bool => null !== $v);

        $data = $this->client->post($path, AuthKeyType::Invoice, $body);

        return new PaymentRegisteredResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }
}
