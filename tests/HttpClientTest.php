<?php

namespace Winpay\Core\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Winpay\Core\Config\WinpayConfig;
use Winpay\Core\Exceptions\HttpException;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Http\HttpClient;

class MockPsr18Client implements ClientInterface
{
    public array $requests = [];
    private array $responses = [];
    private array $exceptions = [];

    public function addResponse(ResponseInterface $response): void
    {
        $this->responses[] = $response;
    }

    public function addException(\Throwable $e): void
    {
        $this->exceptions[] = $e;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if (!empty($this->exceptions)) {
            $e = array_shift($this->exceptions);
            throw $e;
        }

        if (!empty($this->responses)) {
            return array_shift($this->responses);
        }

        throw new \RuntimeException('No response queued');
    }
}

class HttpClientTest extends TestCase
{
    private MockPsr18Client $mockClient;
    private HttpClient $httpClient;
    private WinpayConfig $config;

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $privateKey = '';
        openssl_pkey_export($key, $privateKey);

        $this->config = new WinpayConfig([
            'partner_id' => 'partner-001',
            'merchant_private_key' => $privateKey,
            'channel_id' => 'WEB',
            'base_url' => 'https://sandbox-snap.winpay.id',
        ]);

        $this->mockClient = new MockPsr18Client();
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $this->httpClient = new HttpClient(
            $this->mockClient,
            $requestFactory,
            $streamFactory,
            $this->config,
        );
    }

    private function makeResponse(int $statusCode, array $body): ResponseInterface
    {
        return new \GuzzleHttp\Psr7\Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode($body),
        );
    }

    public function testSuccessfulRequest(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, [
                'responseCode' => '2002700',
                'responseMessage' => 'Successful',
                'virtualAccountData' => [
                    'partnerServiceId' => '12345',
                    'customerNo' => '1234567890',
                ],
            ])
        );

        $result = $this->httpClient->request('POST', '/v1.0/transfer-va/create-va', [
            'partnerServiceId' => '12345',
            'customerNo' => '1234567890',
        ]);

        $this->assertSame('2002700', $result->responseCode);
        $this->assertSame('Successful', $result->responseMessage);
        $this->assertSame(200, $result->httpStatusCode);
        $this->assertTrue($result->isSuccess());

        $this->assertCount(1, $this->mockClient->requests);
        $sentRequest = $this->mockClient->requests[0];

        $this->assertSame('POST', $sentRequest->getMethod());
        $this->assertStringStartsWith('https://sandbox-snap.winpay.id/v1.0/transfer-va/create-va', (string) $sentRequest->getUri());
        $this->assertSame('application/json', $sentRequest->getHeaderLine('Content-Type'));
        $this->assertSame('partner-001', $sentRequest->getHeaderLine('X-PARTNER-ID'));
        $this->assertSame('WEB', $sentRequest->getHeaderLine('CHANNEL-ID'));
        $this->assertNotEmpty($sentRequest->getHeaderLine('X-TIMESTAMP'));
        $this->assertNotEmpty($sentRequest->getHeaderLine('X-SIGNATURE'));
        $this->assertNotEmpty($sentRequest->getHeaderLine('X-EXTERNAL-ID'));
    }

    public function testRequestWithNullBody(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, [
                'responseCode' => '2001100',
                'responseMessage' => 'Successful',
            ])
        );

        $result = $this->httpClient->request('GET', '/v1.0/balance-inquiry', null);

        $this->assertSame(200, $result->httpStatusCode);
        $this->assertTrue($result->isSuccess());
    }

    public function testHttpErrorResponse(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(401, [
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized',
            ])
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        try {
            $this->httpClient->request('POST', '/v1.0/transfer-va/create-va', []);
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
            $this->assertSame('Unauthorized', $e->getMessage());
            throw $e;
        }
    }

    public function testNetworkError(): void
    {
        $this->mockClient->addException(new \RuntimeException('Connection refused'));

        $this->expectException(WinpayException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection refused');

        $this->httpClient->request('POST', '/v1.0/transfer-va/create-va', []);
    }

    public function testInvalidJsonResponse(): void
    {
        $response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], 'not-json');
        $this->mockClient->addResponse($response);

        $this->expectException(WinpayException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->httpClient->request('POST', '/v1.0/transfer-va/create-va', []);
    }

    public function testGeneratedTimestampFormat(): void
    {
        $this->mockClient->addResponse($this->makeResponse(200, ['responseCode' => '00']));

        $this->httpClient->request('POST', '/test', []);

        $ts = $this->mockClient->requests[0]->getHeaderLine('X-TIMESTAMP');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $ts);
    }

    public function testExternalIdFormat(): void
    {
        $this->mockClient->addResponse($this->makeResponse(200, ['responseCode' => '00']));

        $this->httpClient->request('POST', '/test', []);

        $eid = $this->mockClient->requests[0]->getHeaderLine('X-EXTERNAL-ID');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $eid);
    }

    public function testUserAgentHeader(): void
    {
        $this->mockClient->addResponse($this->makeResponse(200, ['responseCode' => '00']));

        $this->httpClient->request('POST', '/test', []);

        $ua = $this->mockClient->requests[0]->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('winpay-core-php/', $ua);
    }

    public function testOnRequestCallback(): void
    {
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $called = false;
        $client = new HttpClient(
            httpClient: $this->mockClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            config: $this->config,
            onRequest: function (string $method, string $url, ?array $body, array $headers, string $stringToSign) use (&$called): void {
                $called = true;
                $this->assertSame('POST', $method);
                $this->assertStringContainsString('/test', $url);
                $this->assertSame(['foo' => 'bar'], $body);
                $this->assertArrayHasKey('User-Agent', $headers);
                $this->assertNotEmpty($stringToSign);
            },
        );

        $this->mockClient->addResponse($this->makeResponse(200, ['responseCode' => '00']));
        $client->request('POST', '/test', ['foo' => 'bar']);

        $this->assertTrue($called);
    }

    public function testOnRequestCallbackExceptionDoesNotBreakRequest(): void
    {
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $client = new HttpClient(
            httpClient: $this->mockClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            config: $this->config,
            onRequest: function (): void {
                throw new \RuntimeException('log failed');
            },
        );

        $this->mockClient->addResponse($this->makeResponse(200, ['responseCode' => '00']));
        $result = $client->request('POST', '/test', []);

        $this->assertSame('00', $result->responseCode);
    }
}
