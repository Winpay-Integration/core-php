<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\RetailService;

class RetailServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/retail/payment', ['amount' => '50000'])
            ->willReturn(new WinpayResponse('2002800', 'Successful', 200));

        $service = new RetailService($client);
        $result = $service->create(['amount' => '50000']);

        $this->assertSame('2002800', $result->responseCode);
    }

    public function testStatus(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/retail/status', ['orderId' => 'ORD-001']);

        $service = new RetailService($client);
        $service->status(['orderId' => 'ORD-001']);
    }

    public function testCancel(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/retail/cancel', ['orderId' => 'ORD-001']);

        $service = new RetailService($client);
        $service->cancel(['orderId' => 'ORD-001']);
    }
}
