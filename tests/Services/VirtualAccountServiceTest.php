<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\VirtualAccountService;

class VirtualAccountServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/transfer-va/create-va', ['test' => 'data'])
            ->willReturn(new WinpayResponse('2002700', 'Successful', 200));

        $service = new VirtualAccountService($client);
        $result = $service->create(['test' => 'data']);

        $this->assertSame('2002700', $result->responseCode);
    }

    public function testInquiry(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/transfer-va/inquiry-va', ['va' => '123']);

        $service = new VirtualAccountService($client);
        $service->inquiry(['va' => '123']);
    }

    public function testStatus(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/v1.0/transfer-va/status', ['va' => '123']);

        $service = new VirtualAccountService($client);
        $service->status(['va' => '123']);
    }

    public function testDelete(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('DELETE', '/v1.0/transfer-va/delete-va', ['va' => '123']);

        $service = new VirtualAccountService($client);
        $service->delete(['va' => '123']);
    }
}
