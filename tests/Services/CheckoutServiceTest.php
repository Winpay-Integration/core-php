<?php

namespace Winpay\Core\Tests\Services;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\CheckoutService;

class CheckoutServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/api/create', ['customer' => ['name' => 'Test']])
            ->willReturn(new WinpayResponse('2010300', 'Invoice created', 200));

        $service = new CheckoutService($client);
        $result = $service->create(['customer' => ['name' => 'Test']]);

        $this->assertSame('2010300', $result->responseCode);
    }

    public function testFind(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', '/api/find/inv-001');

        $service = new CheckoutService($client);
        $service->find('inv-001');
    }

    public function testFindByRef(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', '/api/findByRef/REF-001');

        $service = new CheckoutService($client);
        $service->findByRef('REF-001');
    }

    public function testUpdate(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('PUT', '/api/update/inv-001', ['customer' => ['name' => 'Updated']]);

        $service = new CheckoutService($client);
        $service->update('inv-001', ['customer' => ['name' => 'Updated']]);
    }

    public function testDelete(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('DELETE', '/api/delete/inv-001');

        $service = new CheckoutService($client);
        $service->delete('inv-001');
    }

    public function testDeleteByRef(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('DELETE', '/api/deleteByRef/REF-001');

        $service = new CheckoutService($client);
        $service->deleteByRef('REF-001');
    }
}
