<?php

namespace Winpay\Core\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Enums\EwalletChannel;

class EwalletChannelTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame('SC', EwalletChannel::SPEEDCASH->value);
        $this->assertSame('OVO', EwalletChannel::OVO->value);
        $this->assertSame('DANA', EwalletChannel::DANA->value);
        $this->assertSame('SPAY', EwalletChannel::SHOPEEPAY->value);
        $this->assertSame('ASTRA', EwalletChannel::ASTRAPAY->value);
    }

    public function testCount(): void
    {
        $this->assertCount(5, EwalletChannel::cases());
    }
}
