<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Order\CreateOrderRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Order\OrderItemRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Enum\Currency;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class OrderServiceTest extends TestCase
{
    private const string ORDERS_PATH = '/hub/user/platform/CUSTOM/V1/orders/order';

    private MockObject&iFirmaClientInterface $client;

    private OrderService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new OrderService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    private function makeOrderRequest(string $id = 'ORD-1'): CreateOrderRequest
    {
        return new CreateOrderRequest(
            id: $id,
            status: 'new',
            created: '2024-03-01T10:00:00Z',
            currency: Currency::PLN,
            shippingTotal: 0.0,
            productsTotalNet: 100.0,
            items: [
                new OrderItemRequest(quantity: 1.0, totalPrice: 100.0, price: 100.0),
            ],
        );
    }

    public function testCreatePostsToOrdersEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with(self::ORDERS_PATH, AuthKeyType::Invoice)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'Created', 'Wynik' => 'ORD-1']));

        $result = $this->service->create($this->makeOrderRequest());

        $this->assertSame(0, $result->code);
        $this->assertSame('ORD-1', $result->id);
    }

    public function testUpdatePutsToOrdersEndpointWithOrderId(): void
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with(
                self::ORDERS_PATH,
                AuthKeyType::Invoice,
                $this->callback(static fn (array $body): bool => 'IFIRMA-42' === $body['id']),
            )
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'Updated']));

        $result = $this->service->update('IFIRMA-42', $this->makeOrderRequest());

        $this->assertSame(0, $result->code);
        $this->assertNull($result->id);
    }

    public function testCreatePreservesOriginalCaseInBody(): void
    {
        $capturedBody = [];

        $this->client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->anything(), $this->callback(
                static function (array $body) use (&$capturedBody): true {
                    $capturedBody = $body;

                    return true;
                },
            ))
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $this->service->create($this->makeOrderRequest('ORD-ABC'));

        // Hub API uses camelCase — verify keys are NOT pascal-cased
        $this->assertArrayHasKey('id', $capturedBody);
        $this->assertArrayNotHasKey('Id', $capturedBody);
        $this->assertSame('ORD-ABC', $capturedBody['id']);
    }
}
