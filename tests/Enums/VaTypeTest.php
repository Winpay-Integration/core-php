<?php

namespace Winpay\Core\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Enums\VaType;

class VaTypeTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame('c', VaType::ONE_OFF->value);
        $this->assertSame('o', VaType::OPEN_RECURRING->value);
        $this->assertSame('r', VaType::CLOSE_RECURRING->value);
    }

    public function testFrom(): void
    {
        $this->assertSame(VaType::ONE_OFF, VaType::from('c'));
        $this->assertSame(VaType::OPEN_RECURRING, VaType::from('o'));
        $this->assertSame(VaType::CLOSE_RECURRING, VaType::from('r'));
    }
}
