<?php

namespace Winpay\Core\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\ValidationException;
use Winpay\Core\Exceptions\WinpayException;

class ValidationExceptionTest extends TestCase
{
    public function testExtendsWinpayException(): void
    {
        $exception = new ValidationException('Validation failed', 422);
        $this->assertInstanceOf(WinpayException::class, $exception);
        $this->assertSame('Validation failed', $exception->getMessage());
        $this->assertSame(422, $exception->getCode());
    }
}
