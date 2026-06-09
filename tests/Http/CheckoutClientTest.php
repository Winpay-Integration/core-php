<?php

namespace Winpay\Core\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Winpay\Core\Exceptions\HttpException;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Http\CheckoutClient;
use Winpay\Core\Tests\MockPsr18Client;

class CheckoutClientTest extends TestCase
{
    private MockPsr18Client $mockClient;
    private CheckoutClient $client;

    protected function setUp(): void
    {
        $this->mockClient = new MockPsr18Client();
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $this->client = new CheckoutClient(
            $this->mockClient,
            $requestFactory,
            $streamFactory,
            'https://sandbox-checkout.winpay.id',
            'test-key-123',
            'test-secret-key',
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

    public function testCheckoutHeaders(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, [
                'responseCode' => '2010300',
                'responseMessage' => 'Invoice created',
            ])
        );

        $result = $this->client->request('POST', '/api/create', ['customer' => ['name' => 'Test']]);

        $this->assertSame('2010300', $result->responseCode);

        $request = $this->mockClient->requests[0];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringStartsWith('https://sandbox-checkout.winpay.id/api/create', (string) $request->getUri());
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame('test-key-123', $request->getHeaderLine('X-Winpay-Key'));
        $this->assertNotEmpty($request->getHeaderLine('X-Winpay-Timestamp'));
        $this->assertNotEmpty($request->getHeaderLine('X-Winpay-Signature'));
    }

    public function testHmacSignatureFormat(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, ['responseCode' => '2000300'])
        );

        $this->client->request('GET', '/api/test');

        $request = $this->mockClient->requests[0];
        $ts = $request->getHeaderLine('X-Winpay-Timestamp');
        $sig = $request->getHeaderLine('X-Winpay-Signature');

        $expected = hash_hmac('sha256', $ts, 'test-secret-key');
        $this->assertSame($expected, $sig);
    }

    public function testHttpGetWithoutBody(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, [
                'responseCode' => '2000300',
                'responseMessage' => 'Invoice Found',
            ])
        );

        $result = $this->client->request('GET', '/api/find/abc-123');

        $this->assertSame('2000300', $result->responseCode);
    }

    public function testHttpErrorResponse(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(401, [
                'responseCode' => '4010300',
                'responseMessage' => 'Unauthorized',
            ])
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        try {
            $this->client->request('POST', '/api/create', []);
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

        $this->client->request('POST', '/api/create', []);
    }

    public function testUserAgentHeader(): void
    {
        $this->mockClient->addResponse(
            $this->makeResponse(200, ['responseCode' => '2000300'])
        );

        $this->client->request('GET', '/api/test');

        $ua = $this->mockClient->requests[0]->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('winpay-core-php/', $ua);
    }

    public function testOnRequestCallback(): void
    {
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $called = false;
        $client = new CheckoutClient(
            httpClient: $this->mockClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            baseUrl: 'https://sandbox-checkout.winpay.id',
            key: 'test-key',
            secretKey: 'test-secret',
            onRequest: function (string $method, string $url, ?array $body, array $headers, string $stringToSign) use (&$called): void {
                $called = true;
                $this->assertSame('POST', $method);
                $this->assertStringContainsString('/api/create', $url);
                $this->assertSame(['foo' => 'bar'], $body);
                $this->assertArrayHasKey('User-Agent', $headers);
                $this->assertNotEmpty($stringToSign);
            },
        );

        $this->mockClient->addResponse(
            $this->makeResponse(200, ['responseCode' => '2010300'])
        );
        $client->request('POST', '/api/create', ['foo' => 'bar']);

        $this->assertTrue($called);
    }

    public function testOnRequestCallbackExceptionDoesNotBreakRequest(): void
    {
        $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

        $client = new CheckoutClient(
            httpClient: $this->mockClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            baseUrl: 'https://sandbox-checkout.winpay.id',
            key: 'test-key',
            secretKey: 'test-secret',
            onRequest: function (): void {
                throw new \RuntimeException('log failed');
            },
        );

        $this->mockClient->addResponse(
            $this->makeResponse(200, ['responseCode' => '2000300'])
        );
        $result = $client->request('POST', '/api/create', []);

        $this->assertSame('2000300', $result->responseCode);
    }
}
