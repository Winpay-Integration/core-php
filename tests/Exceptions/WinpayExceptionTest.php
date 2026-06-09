<?php

namespace Winpay\Core\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\WinpayException;

class WinpayExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new WinpayException('Test error', 42);

        $this->assertSame('Test error', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertNull($exception->getErrors());
    }

    public function testWithErrors(): void
    {
        $errors = ['field' => 'required', 'amount' => 'invalid'];

        $exception = new WinpayException('Validation failed', 422, null, $errors);

        $this->assertSame($errors, $exception->getErrors());
    }

    public function testPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');

        $exception = new WinpayException('Wrapped error', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExtendsException(): void
    {
        $exception = new WinpayException('test');
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
