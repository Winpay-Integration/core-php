<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\EwalletService;

class EwalletServiceTest extends TestCase
{
    public function testCreatePayment(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/debit/payment-host-to-host', ['amount' => '25000'])
            ->willReturn(new WinpayResponse('2005400', 'Successful', 200));

        $service = new EwalletService($client);
        $result = $service->createPayment(['amount' => '25000']);

        $this->assertSame('2005400', $result->responseCode);
    }

    public function testStatus(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/debit/status', ['orderId' => 'ORD-001']);

        $service = new EwalletService($client);
        $service->status(['orderId' => 'ORD-001']);
    }

    public function testCancel(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/debit/cancel', ['orderId' => 'ORD-001']);

        $service = new EwalletService($client);
        $service->cancel(['orderId' => 'ORD-001']);
    }
}
