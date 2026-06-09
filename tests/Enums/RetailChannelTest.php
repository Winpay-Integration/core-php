<?php

namespace Winpay\Core\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Enums\RetailChannel;

class RetailChannelTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame('INDOMARET', RetailChannel::INDOMARET->value);
        $this->assertSame('ALFAMART', RetailChannel::ALFAMART->value);
        $this->assertSame('FASTPAY', RetailChannel::FASTPAY->value);
    }

    public function testCount(): void
    {
        $this->assertCount(3, RetailChannel::cases());
    }
}
