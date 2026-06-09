<?php

namespace Winpay\Core\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\HttpException;
use Winpay\Core\Exceptions\WinpayException;

class HttpExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpException('Not Found', 404, null, 404);

        $this->assertSame('Not Found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertSame(404, $exception->getStatusCode());
    }

    public function testExtendsWinpayException(): void
    {
        $exception = new HttpException('error', 400, null, 400);
        $this->assertInstanceOf(WinpayException::class, $exception);
    }

    public function testStatusCode(): void
    {
        $exception = new HttpException('Internal Server Error', 500, null, 500);
        $this->assertSame(500, $exception->getStatusCode());
    }

    public function testStatusCodeDefaultNull(): void
    {
        $exception = new HttpException('error');
        $this->assertNull($exception->getStatusCode());
    }
}
