<?php

namespace Winpay\Core\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\AuthenticationException;
use Winpay\Core\Exceptions\WinpayException;

class AuthenticationExceptionTest extends TestCase
{
    public function testExtendsWinpayException(): void
    {
        $exception = new AuthenticationException('Unauthorized', 401);
        $this->assertInstanceOf(WinpayException::class, $exception);
        $this->assertSame('Unauthorized', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }
}
