<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Client;

use const INF;
use const JSON_THROW_ON_ERROR;

use JsonException;
use maciejlewandowskii\iFirmaApi\Authentication\AuthenticatorInterface;
use maciejlewandowskii\iFirmaApi\Authentication\Credentials;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClient;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use maciejlewandowskii\iFirmaApi\Exception\HttpException;
use maciejlewandowskii\iFirmaApi\Exception\RateLimitException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class iFirmaClientTest extends TestCase
{
    private MockObject&AuthenticatorInterface $authenticator;

    private Credentials $credentials;

    protected function setUp(): void
    {
        $this->authenticator = $this->createMock(AuthenticatorInterface::class);
        $this->authenticator->method('buildAuthorizationHeader')
            ->willReturn('IAPIS user=test, hmac-sha1=abc123');

        $this->credentials = new Credentials(
            username: 'testuser',
            invoiceKey: 'aabbccdd11223344',
            subscriberKey: 'aabbccdd11223344',
        );
    }

    private function makeClient(MockResponse $response): iFirmaClient
    {
        return new iFirmaClient(
            new MockHttpClient($response),
            $this->authenticator,
            $this->credentials,
        );
    }

    private static function j(mixed $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Test JSON encoding failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function testGetReturnsApiResponseWithUnwrappedData(): void
    {
        $client = $this->makeClient(new MockResponse(
            self::j(['response' => ['Kod' => 0, 'MiesiacKsiegowy' => 3, 'RokKsiegowy' => 2024]]),
            ['http_code' => 200],
        ));

        $result = $client->get('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber);

        $this->assertSame(0, $result->getInt('Kod'));
        $this->assertSame(3, $result->getInt('MiesiacKsiegowy'));
        $this->assertSame(2024, $result->getInt('RokKsiegowy'));
    }

    public function testGetWithoutEnvelopeAlsoWorks(): void
    {
        $client = $this->makeClient(new MockResponse(
            self::j(['Kod' => 0, 'Wynik' => 'FV/2024/1']),
            ['http_code' => 200],
        ));

        $result = $client->get('/fakturakraj/1.json', AuthKeyType::Invoice);

        $this->assertSame('FV/2024/1', $result->getString('Wynik'));
    }

    public function testPostSendsBodyAndReturnsApiResponse(): void
    {
        $sentBody = null;
        $mockResponse = new MockResponse(
            self::j(['Kod' => 0, 'Informacja' => 'Created', 'Identyfikator' => 'FV/1']),
            ['http_code' => 200],
        );

        $httpClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use ($mockResponse, &$sentBody): MockResponse {
                $sentBody = $options['body'];

                return $mockResponse;
            },
        );

        $client = new iFirmaClient($httpClient, $this->authenticator, $this->credentials);
        $result = $client->post('/fakturakraj.json', AuthKeyType::Invoice, ['Nazwa' => 'Test']);

        $this->assertSame('FV/1', $result->getString('Identyfikator'));
        $this->assertIsString($sentBody);
        $this->assertStringContainsString('Test', $sentBody);
    }

    public function testPutReturnsApiResponse(): void
    {
        $client = $this->makeClient(new MockResponse(
            self::j(['Kod' => 0, 'Informacja' => 'Updated']),
            ['http_code' => 200],
        ));

        $result = $client->put('/kontrahenci/123.json', AuthKeyType::Invoice, ['Nazwa' => 'Updated']);

        $this->assertSame(0, $result->getInt('Kod'));
        $this->assertSame('Updated', $result->getString('Informacja'));
    }

    public function testGetRawWithQueryParamsSendsThemInRequest(): void
    {
        $capturedOptions = [];
        $httpClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$capturedOptions): MockResponse {
                $capturedOptions = $options;

                return new MockResponse('%PDF-1.4', ['http_code' => 200]);
            },
        );

        $client = new iFirmaClient($httpClient, $this->authenticator, $this->credentials);
        $client->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice, ['typ' => 'dup']);

        $this->assertArrayHasKey('query', $capturedOptions);
        $this->assertSame(['typ' => 'dup'], $capturedOptions['query']);
    }

    public function testGetRawReturnsStringContent(): void
    {
        $pdfContent = '%PDF-1.4 fake pdf content';
        $client = $this->makeClient(new MockResponse($pdfContent, ['http_code' => 200]));

        $result = $client->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice);

        $this->assertSame($pdfContent, $result);
    }

    public function testHttp401ThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageMatches('/Authentication failed.*401/');

        $this->makeClient(new MockResponse('', ['http_code' => 401]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testHttp403ThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageMatches('/Authentication failed.*403/');

        $this->makeClient(new MockResponse('', ['http_code' => 403]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testHttp429ThrowsRateLimitException(): void
    {
        $this->expectException(RateLimitException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 429]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testHttp500ThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 500]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testHttp404ThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 404]))
            ->get('/some-path.json', AuthKeyType::Invoice);
    }

    public function testInvalidJsonBodyThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);

        $this->makeClient(new MockResponse('not-json', ['http_code' => 200]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testNonObjectJsonThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessageMatches('/Unexpected iFirma response/');

        // JSON string (not object) should be rejected
        $this->makeClient(new MockResponse(self::j('just-a-string'), ['http_code' => 200]))
            ->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testNonZeroKodThrowsApiException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/Nieprawidłowy/');

        $this->makeClient(new MockResponse(
            self::j(['Kod' => 101, 'Informacja' => 'Nieprawidłowy NIP']),
            ['http_code' => 200],
        ))->post('/fakturakraj.json', AuthKeyType::Invoice, []);
    }

    public function testApiExceptionCodeMatchesKodField(): void
    {
        $client = $this->makeClient(new MockResponse(
            self::j(['Kod' => 42, 'Informacja' => 'Error 42']),
            ['http_code' => 200],
        ));

        try {
            $client->get('/any.json', AuthKeyType::Invoice);
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertSame(42, $e->getApiCode());
        }
    }

    public function testGetRawHttp401ThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 401]))
            ->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice);
    }

    public function testGetRawHttp429ThrowsRateLimitException(): void
    {
        $this->expectException(RateLimitException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 429]))
            ->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice);
    }

    public function testGetRawHttp500ThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);

        $this->makeClient(new MockResponse('', ['http_code' => 500]))
            ->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice);
    }

    public function testTransportExceptionWrappedInHttpExceptionForGet(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessageMatches('/iFirma API request failed/');

        $client = new iFirmaClient(
            new MockHttpClient(static function (): never { throw new RuntimeException('Connection refused'); }),
            $this->authenticator,
            $this->credentials,
        );
        $client->get('/fakturakraj.json', AuthKeyType::Invoice);
    }

    public function testTransportExceptionWrappedInHttpExceptionForGetRaw(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessageMatches('/iFirma API request failed/');

        $client = new iFirmaClient(
            new MockHttpClient(static function (): never { throw new RuntimeException('Connection refused'); }),
            $this->authenticator,
            $this->credentials,
        );
        $client->getRaw('/fakturakraj/1.pdf', AuthKeyType::Invoice);
    }

    public function testBodyEncodingFailureThrowsHttpException(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessageMatches('/Failed to encode request body/');

        // INF cannot be JSON-encoded — triggers JsonException with JSON_THROW_ON_ERROR
        $client = new iFirmaClient(new MockHttpClient(), $this->authenticator, $this->credentials);
        $client->post('/x.json', AuthKeyType::Invoice, ['value' => INF]);
    }

    public function testQueryParamsArePassedForGet(): void
    {
        $capturedUrl = '';
        $httpClient = new MockHttpClient(
            static function (string $method, string $url) use (&$capturedUrl): MockResponse {
                $capturedUrl = $url;

                return new MockResponse(self::j(['Kod' => 0]), ['http_code' => 200]);
            },
        );

        $client = new iFirmaClient($httpClient, $this->authenticator, $this->credentials);
        $client->get('/faktury.json', AuthKeyType::Invoice, ['strona' => 1]);

        $this->assertStringContainsString('strona', $capturedUrl);
    }
}
