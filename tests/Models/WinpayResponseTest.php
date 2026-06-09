<?php

namespace Winpay\Core\Tests\Models;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Models\WinpayResponse;

class WinpayResponseTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $response = new WinpayResponse(
            responseCode: '2002700',
            responseMessage: 'Successful',
            httpStatusCode: 200,
            data: ['vaNumber' => '123456'],
        );

        $this->assertSame('2002700', $response->responseCode);
        $this->assertSame('Successful', $response->responseMessage);
        $this->assertSame(200, $response->httpStatusCode);
        $this->assertSame(['vaNumber' => '123456'], $response->data);
    }

    public function testIsSuccess(): void
    {
        $success = new WinpayResponse('00', 'OK', 200);
        $this->assertTrue($success->isSuccess());

        $redirect = new WinpayResponse('00', 'OK', 302);
        $this->assertFalse($redirect->isSuccess());

        $clientError = new WinpayResponse('40', 'Bad Request', 400);
        $this->assertFalse($clientError->isSuccess());

        $serverError = new WinpayResponse('50', 'Error', 500);
        $this->assertFalse($serverError->isSuccess());
    }

    public function testGet(): void
    {
        $response = new WinpayResponse(
            responseCode: '2002700',
            responseMessage: 'Success',
            httpStatusCode: 200,
            data: ['key1' => 'value1', 'nested' => ['inner' => 'value']],
        );

        $this->assertSame('value1', $response->get('key1'));
        $this->assertSame(['inner' => 'value'], $response->get('nested'));
        $this->assertNull($response->get('nonexistent'));
        $this->assertSame('default', $response->get('nonexistent', 'default'));
    }

    public function testNullableResponseCode(): void
    {
        $response = new WinpayResponse(null, null, 500);

        $this->assertNull($response->responseCode);
        $this->assertNull($response->responseMessage);
        $this->assertFalse($response->isSuccess());
    }

    public function testEmptyData(): void
    {
        $response = new WinpayResponse('00', 'OK', 200);
        $this->assertSame([], $response->data);
    }
}
