<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\ReportService;

class ReportServiceTest extends TestCase
{
    public function testBalance(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/balance-inquiry', ['account' => '123'])
            ->willReturn(new WinpayResponse('2001100', 'Successful', 200));

        $service = new ReportService($client);
        $result = $service->balance(['account' => '123']);

        $this->assertSame('2001100', $result->responseCode);
    }

    public function testTransactionHistory(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/transaction-history-list', ['from' => '2024-01-01']);

        $service = new ReportService($client);
        $service->transactionHistory(['from' => '2024-01-01']);
    }

    public function testBankStatement(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/bank-statement', ['from' => '2024-01-01']);

        $service = new ReportService($client);
        $service->bankStatement(['from' => '2024-01-01']);
    }
}
