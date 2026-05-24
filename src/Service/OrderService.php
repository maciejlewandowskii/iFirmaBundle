<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Request\Order\CreateOrderRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Order\OrderCreatedResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class OrderService extends AbstractService
{
    private const string ORDERS_PATH = '/hub/user/platform/CUSTOM/V1/orders/order';

    /**
     * @throws ExceptionInterface
     */
    public function create(CreateOrderRequest $request): OrderCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post(self::ORDERS_PATH, AuthKeyType::Invoice, $this->toArrayPreservingCase($request));

        return new OrderCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function update(string $orderId, CreateOrderRequest $request): OrderCreatedResponse
    {
        $this->validate($request);

        $body = $this->toArrayPreservingCase($request);
        $body['id'] = $orderId;

        $data = $this->client->put(self::ORDERS_PATH, AuthKeyType::Invoice, $body);

        return new OrderCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            id: $data->getNullableString('Wynik'),
        );
    }
}
