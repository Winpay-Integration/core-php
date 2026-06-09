<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\QrisService;

class QrisServiceTest extends TestCase
{
    public function testGenerate(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/qr/qr-mpm-generate', ['amount' => '50000'])
            ->willReturn(new WinpayResponse('2004700', 'Successful', 200));

        $service = new QrisService($client);
        $result = $service->generate(['amount' => '50000']);

        $this->assertSame('2004700', $result->responseCode);
    }

    public function testQuery(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/qr/qr-mpm-query', ['orderId' => 'ORD-001']);

        $service = new QrisService($client);
        $service->query(['orderId' => 'ORD-001']);
    }

    public function testCancel(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/qr/qr-mpm-cancel', ['orderId' => 'ORD-001']);

        $service = new QrisService($client);
        $service->cancel(['orderId' => 'ORD-001']);
    }
}
