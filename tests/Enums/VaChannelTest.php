<?php

namespace Winpay\Core\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Enums\VaChannel;

class VaChannelTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame('BRI', VaChannel::BRI->value);
        $this->assertSame('BNI', VaChannel::BNI->value);
        $this->assertSame('MANDIRI', VaChannel::MANDIRI->value);
        $this->assertSame('MANDIRIPC', VaChannel::MANDIRI_PC->value);
        $this->assertSame('PERMATA', VaChannel::PERMATA->value);
        $this->assertSame('BSI', VaChannel::BSI->value);
        $this->assertSame('MUAMALAT', VaChannel::MUAMALAT->value);
        $this->assertSame('BCA', VaChannel::BCA->value);
        $this->assertSame('CIMB', VaChannel::CIMB->value);
        $this->assertSame('SINARMAS', VaChannel::SINARMAS->value);
        $this->assertSame('BNC', VaChannel::BNC->value);
        $this->assertSame('MAYBANK', VaChannel::MAYBANK->value);
    }

    public function testCount(): void
    {
        $this->assertCount(12, VaChannel::cases());
    }
}
